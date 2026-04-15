<?php

namespace App\Actions\EO;

use App\Jobs\ProcessPaidEventTransaction;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImportParticipantsCsvAction
{
    public function execute(Event $event, UploadedFile $file, array $options, User $operator): array
    {
        $dryRun = (bool) ($options['dry_run'] ?? true);
        $sendEmailIfPaid = (bool) ($options['send_email_if_paid'] ?? true);
        $useQueue = (bool) ($options['use_queue'] ?? true);

        $path = $file->getRealPath();
        if (! $path) {
            throw ValidationException::withMessages(['file' => ['File tidak valid.']]);
        }

        $csv = new \SplFileObject($path);
        $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        $header = null;
        $rows = [];
        $rowNumber = 0;

        foreach ($csv as $row) {
            $rowNumber++;
            if (! is_array($row)) {
                continue;
            }

            if ($rowNumber === 1) {
                $header = $this->normalizeHeader($row);
                continue;
            }

            if (! $header) {
                break;
            }

            $assoc = $this->rowToAssoc($header, $row);
            if ($this->isEmptyRow($assoc)) {
                continue;
            }

            $assoc['__row_number'] = $rowNumber;
            $rows[] = $assoc;
        }

        if (! $header) {
            throw ValidationException::withMessages(['file' => ['CSV header tidak ditemukan.']]);
        }

        $required = [
            'group_key',
            'pic_name',
            'pic_email',
            'pic_phone',
            'name',
            'email',
            'phone',
            'gender',
            'category_id',
            'id_card',
            'address',
            'payment_status',
        ];
        foreach ($required as $col) {
            if (! in_array($col, $header, true)) {
                throw ValidationException::withMessages(['file' => ["Kolom wajib tidak ditemukan: {$col}"]]);
            }
        }

        $errors = [];
        $byGroup = [];
        $seenInFile = [];
        $seenBibInFile = [];

        foreach ($rows as $r) {
            $rn = (int) $r['__row_number'];
            $r = $this->normalizeRow($r);

            $groupKey = (string) ($r['group_key'] ?? '');
            if ($groupKey === '') {
                $groupKey = 'ROW-'.$rn;
            }
            $r['group_key'] = $groupKey;

            $categoryId = (int) ($r['category_id'] ?? 0);
            if ($categoryId <= 0) {
                $errors[] = $this->err($rn, 'category_id tidak valid.');
                continue;
            }

            $name = (string) ($r['name'] ?? '');
            if ($name === '' || mb_strlen($name) > 255) {
                $errors[] = $this->err($rn, 'name wajib diisi dan maksimal 255 karakter.');
                continue;
            }

            $email = (string) ($r['email'] ?? '');
            if ($email === '' || mb_strlen($email) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $this->err($rn, 'email peserta tidak valid atau terlalu panjang.');
                continue;
            }

            $phone = (string) ($r['phone'] ?? '');
            if ($phone === '' || ! preg_match('/^[0-9]+$/', $phone) || mb_strlen($phone) < 10 || mb_strlen($phone) > 15) {
                $errors[] = $this->err($rn, 'phone peserta harus angka, min 10 dan max 15 digit.');
                continue;
            }

            $gender = (string) ($r['gender'] ?? '');
            if ($gender !== '' && ! in_array($gender, ['male', 'female'], true)) {
                $errors[] = $this->err($rn, 'gender harus male atau female (atau kosong).');
                continue;
            }
            if ($gender === '') {
                $r['gender'] = null;
            }

            $idCard = (string) ($r['id_card'] ?? '');
            if ($idCard === '' || mb_strlen($idCard) > 50) {
                $errors[] = $this->err($rn, 'id_card wajib diisi dan maksimal 50 karakter.');
                continue;
            }

            $address = (string) ($r['address'] ?? '');
            if ($address === '' || mb_strlen($address) > 500) {
                $errors[] = $this->err($rn, 'address wajib diisi dan maksimal 500 karakter.');
                continue;
            }

            if ($r['city'] !== '' && mb_strlen((string) $r['city']) > 100) {
                $errors[] = $this->err($rn, 'city maksimal 100 karakter.');
                continue;
            }
            if ($r['province'] !== '' && mb_strlen((string) $r['province']) > 100) {
                $errors[] = $this->err($rn, 'province maksimal 100 karakter.');
                continue;
            }
            if ($r['postal_code'] !== '' && mb_strlen((string) $r['postal_code']) > 20) {
                $errors[] = $this->err($rn, 'postal_code maksimal 20 karakter.');
                continue;
            }
            if ($r['emergency_contact_name'] !== '' && mb_strlen((string) $r['emergency_contact_name']) > 255) {
                $errors[] = $this->err($rn, 'emergency_contact_name maksimal 255 karakter.');
                continue;
            }
            if ($r['emergency_contact_number'] !== '') {
                $ecn = (string) $r['emergency_contact_number'];
                $ecn = preg_replace('/\D+/', '', $ecn);
                if ($ecn === '' || mb_strlen($ecn) < 10 || mb_strlen($ecn) > 15) {
                    $errors[] = $this->err($rn, 'emergency_contact_number harus angka, min 10 dan max 15 digit.');
                    continue;
                }
                $r['emergency_contact_number'] = $ecn;
            }

            if ($r['date_of_birth'] !== '') {
                try {
                    $dob = Carbon::parse((string) $r['date_of_birth']);
                    if (! $dob->isBefore(Carbon::today())) {
                        $errors[] = $this->err($rn, 'date_of_birth harus sebelum hari ini.');
                        continue;
                    }
                } catch (\Throwable $e) {
                    $errors[] = $this->err($rn, 'date_of_birth tidak valid.');
                    continue;
                }
            }

            if ($r['target_time'] !== '') {
                $tt = (string) $r['target_time'];
                if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $tt) || $tt === '00:00:00') {
                    $errors[] = $this->err($rn, 'target_time harus format HH:MM:SS dan bukan 00:00:00.');
                    continue;
                }
            }

            if ($r['jersey_size'] !== '' && mb_strlen((string) $r['jersey_size']) > 10) {
                $errors[] = $this->err($rn, 'jersey_size maksimal 10 karakter.');
                continue;
            }

            if ($r['bib_number'] !== '') {
                $bib = (string) $r['bib_number'];
                if (mb_strlen($bib) > 20) {
                    $errors[] = $this->err($rn, 'bib_number maksimal 20 karakter.');
                    continue;
                }
                $bibKey = strtolower($bib);
                if (isset($seenBibInFile[$bibKey])) {
                    $errors[] = $this->err($rn, 'Duplikat bib_number di CSV.');
                    continue;
                }
                $seenBibInFile[$bibKey] = true;
                if (Participant::where('bib_number', $bib)->exists()) {
                    $errors[] = $this->err($rn, 'bib_number sudah digunakan.');
                    continue;
                }
            }

            $keyInFile = strtolower($idCard).'|'.$categoryId;
            if (isset($seenInFile[$keyInFile])) {
                $errors[] = $this->err($rn, 'Duplikat id_card + category_id di CSV.');
                continue;
            }
            $seenInFile[$keyInFile] = true;

            $status = (string) ($r['payment_status'] ?? 'pending');
            $status = strtolower(trim($status));
            if ($status === '') {
                $status = 'pending';
            }
            if (in_array($status, ['settlement', 'capture'], true)) {
                $status = 'paid';
            }
            if (! in_array($status, ['paid', 'pending', 'cod'], true)) {
                $errors[] = $this->err($rn, 'payment_status harus salah satu: paid, pending, cod.');
                continue;
            }
            $r['payment_status'] = $status;

            $byGroup[$groupKey][] = $r;
        }

        $groups = [];
        foreach ($byGroup as $groupKey => $items) {
            $picEmail = null;
            $picName = null;
            $picPhone = null;
            $paymentStatus = null;
            $couponCode = null;

            foreach ($items as $r) {
                $rn = (int) $r['__row_number'];

                $rPicEmail = strtolower(trim((string) ($r['pic_email'] ?? '')));
                $rPicName = trim((string) ($r['pic_name'] ?? ''));
                $rPicPhone = preg_replace('/\D+/', '', (string) ($r['pic_phone'] ?? ''));

                if ($rPicEmail === '' || mb_strlen($rPicEmail) > 255 || ! filter_var($rPicEmail, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = $this->err($rn, 'pic_email tidak valid atau terlalu panjang.');
                    continue 2;
                }
                if ($rPicName === '' || mb_strlen($rPicName) > 255) {
                    $errors[] = $this->err($rn, 'pic_name wajib diisi dan maksimal 255 karakter.');
                    continue 2;
                }
                if ($rPicPhone === '' || ! preg_match('/^[0-9]+$/', $rPicPhone) || mb_strlen($rPicPhone) < 10 || mb_strlen($rPicPhone) > 15) {
                    $errors[] = $this->err($rn, 'pic_phone harus angka, min 10 dan max 15 digit.');
                    continue 2;
                }

                $rStatus = (string) ($r['payment_status'] ?? 'pending');
                $rCoupon = trim((string) ($r['coupon_code'] ?? ''));

                if ($picEmail === null) {
                    $picEmail = $rPicEmail;
                    $picName = $rPicName;
                    $picPhone = $rPicPhone;
                    $paymentStatus = $rStatus;
                    $couponCode = $rCoupon;
                } else {
                    if ($picEmail !== $rPicEmail || $picName !== $rPicName || $picPhone !== $rPicPhone) {
                        $errors[] = $this->err($rn, 'Dalam 1 group_key, PIC harus konsisten (pic_name/email/phone).');
                        continue 2;
                    }
                    if ($paymentStatus !== $rStatus) {
                        $errors[] = $this->err($rn, 'Dalam 1 group_key, payment_status harus sama.');
                        continue 2;
                    }
                    if ($couponCode !== $rCoupon) {
                        $errors[] = $this->err($rn, 'Dalam 1 group_key, coupon_code harus sama.');
                        continue 2;
                    }
                }
            }

            $groups[] = [
                'group_key' => $groupKey,
                'pic_name' => $picName,
                'pic_email' => $picEmail,
                'pic_phone' => $picPhone,
                'payment_status' => $paymentStatus,
                'coupon_code' => $couponCode,
                'items' => $items,
            ];
        }

        if ($dryRun) {
            return [
                'success' => count($errors) === 0,
                'dry_run' => true,
                'summary' => [
                    'rows' => count($rows),
                    'groups' => count($groups),
                    'errors' => count($errors),
                    'created_transactions' => 0,
                    'created_participants' => 0,
                    'emailed_paid' => 0,
                ],
                'errors' => $errors,
            ];
        }

        $createdTransactions = 0;
        $createdParticipants = 0;
        $emailedPaid = 0;
        $skippedExisting = 0;

        foreach ($groups as $g) {
            $coupon = null;
            if (! empty($g['coupon_code'])) {
                $coupon = Coupon::query()
                    ->where('event_id', $event->id)
                    ->where('code', $g['coupon_code'])
                    ->first();
                if (! $coupon) {
                    foreach ($g['items'] as $it) {
                        $errors[] = $this->err((int) $it['__row_number'], 'coupon_code tidak ditemukan untuk event ini.');
                    }
                    continue;
                }
            }

            $items = $g['items'];
            $categoryIds = collect($items)->map(fn ($r) => (int) $r['category_id'])->unique()->values()->all();
            sort($categoryIds);

            try {
                $transaction = DB::transaction(function () use ($event, $operator, $g, $items, $categoryIds, $coupon, &$createdParticipants, &$skippedExisting) {
                    $categories = RaceCategory::query()
                        ->whereIn('id', $categoryIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($categoryIds as $cid) {
                        $cat = $categories->get($cid);
                        if (! $cat || (int) $cat->event_id !== (int) $event->id) {
                            throw ValidationException::withMessages(['file' => ["Kategori tidak valid untuk event ini: {$cid}"]]);
                        }
                    }

                    $effectivePaidStatus = in_array($g['payment_status'], ['paid', 'cod'], true) ? $g['payment_status'] : 'pending';
                    $countsForQuota = $effectivePaidStatus === 'paid' || $effectivePaidStatus === 'cod';

                    if ($countsForQuota) {
                        $quantities = [];
                        foreach ($items as $r) {
                            $cid = (int) $r['category_id'];
                            $quantities[$cid] = ($quantities[$cid] ?? 0) + 1;
                        }

                        foreach ($quantities as $cid => $qty) {
                            $cat = $categories->get($cid);
                            $registered = Participant::where('race_category_id', $cid)
                                ->whereHas('transaction', function ($q) {
                                    $q->whereIn('payment_status', ['paid', 'cod']);
                                })
                                ->count();

                            if ($cat->quota && ($registered + $qty) > $cat->quota) {
                                throw ValidationException::withMessages(['file' => ["Kuota kategori '{$cat->name}' tidak mencukupi."]]);
                            }
                        }
                    }

                    $totalOriginal = 0;
                    $participantPayloads = [];
                    foreach ($items as $r) {
                        $cid = (int) $r['category_id'];
                        $cat = $categories->get($cid);

                        $idCard = (string) $r['id_card'];
                        $already = Participant::where('race_category_id', $cid)
                            ->where('id_card', $idCard)
                            ->whereHas('transaction', function ($q) use ($event) {
                                $q->where('event_id', $event->id);
                            })
                            ->exists();
                        if ($already) {
                            $skippedExisting++;
                            continue;
                        }

                        $priceInfo = $this->getCategoryPrice($cat);
                        $price = (int) ($priceInfo['price'] ?? 0);
                        $priceType = (string) ($priceInfo['type'] ?? 'regular');

                        $totalOriginal += $price;

                        $participantPayloads[] = [
                            'race_category_id' => $cid,
                            'name' => (string) $r['name'],
                            'gender' => $r['gender'] !== '' ? $r['gender'] : null,
                            'phone' => (string) $r['phone'],
                            'email' => (string) $r['email'],
                            'id_card' => (string) $r['id_card'],
                            'address' => (string) $r['address'],
                            'city' => $r['city'] !== '' ? (string) $r['city'] : null,
                            'province' => $r['province'] !== '' ? (string) $r['province'] : null,
                            'postal_code' => $r['postal_code'] !== '' ? (string) $r['postal_code'] : null,
                            'emergency_contact_name' => $r['emergency_contact_name'] !== '' ? (string) $r['emergency_contact_name'] : null,
                            'emergency_contact_number' => $r['emergency_contact_number'] !== '' ? (string) $r['emergency_contact_number'] : null,
                            'date_of_birth' => $r['date_of_birth'] !== '' ? (string) $r['date_of_birth'] : null,
                            'target_time' => $r['target_time'] !== '' ? (string) $r['target_time'] : null,
                            'jersey_size' => $r['jersey_size'] !== '' ? (string) $r['jersey_size'] : null,
                            'bib_number' => $r['bib_number'] !== '' ? (string) $r['bib_number'] : null,
                            'photo' => null,
                            'addons' => [],
                            'status' => 'pending',
                            'is_picked_up' => false,
                            'price_type' => $priceType,
                            'event_package_id' => null,
                        ];
                    }

                    if (count($participantPayloads) === 0) {
                        return null;
                    }

                    $discountAmount = 0;
                    if ($coupon) {
                        $discountAmount = $coupon->applyDiscount($totalOriginal);
                    }

                    $finalAmount = max(0, (int) $totalOriginal - (int) $discountAmount);

                    $paymentStatus = $g['payment_status'];
                    $paidAt = null;
                    if ($paymentStatus === 'paid') {
                        $paidAt = now();
                    }

                    $transaction = Transaction::create([
                        'event_id' => $event->id,
                        'user_id' => $operator->id,
                        'pic_data' => [
                            'name' => $g['pic_name'],
                            'email' => $g['pic_email'],
                            'phone' => $g['pic_phone'],
                            'manual_entry' => true,
                            'import_csv' => true,
                            'group_key' => $g['group_key'],
                            'send_whatsapp' => false,
                        ],
                        'total_original' => $totalOriginal,
                        'coupon_id' => $coupon?->id,
                        'discount_amount' => (int) $discountAmount,
                        'admin_fee' => 0,
                        'final_amount' => $finalAmount,
                        'payment_status' => $paymentStatus,
                        'paid_at' => $paidAt,
                        'payment_gateway' => 'manual_csv',
                        'payment_channel' => 'manual_csv',
                        'unique_code' => 0,
                    ]);

                    foreach ($participantPayloads as $payload) {
                        $payload['transaction_id'] = $transaction->id;
                        Participant::create($payload);
                        $createdParticipants++;
                    }

                    return $transaction;
                }, 3);

                if (! $transaction) {
                    continue;
                }

                $createdTransactions++;

                if ($sendEmailIfPaid && $transaction->payment_status === 'paid') {
                    if ($useQueue) {
                        ProcessPaidEventTransaction::dispatch($transaction);
                    } else {
                        ProcessPaidEventTransaction::dispatchSync($transaction);
                    }
                    $emailedPaid++;
                }
            } catch (ValidationException $e) {
                foreach ($g['items'] as $it) {
                    $errors[] = $this->err((int) $it['__row_number'], implode(' ', $e->errors()['file'] ?? ['Gagal import group.']));
                }
            } catch (\Throwable $e) {
                foreach ($g['items'] as $it) {
                    $errors[] = $this->err((int) $it['__row_number'], 'Gagal import group: '.$e->getMessage());
                }
            }
        }

        return [
            'success' => count($errors) === 0,
            'dry_run' => false,
            'summary' => [
                'rows' => count($rows),
                'groups' => count($groups),
                'errors' => count($errors),
                'created_transactions' => $createdTransactions,
                'created_participants' => $createdParticipants,
                'emailed_paid' => $emailedPaid,
                'skipped_existing' => $skippedExisting,
            ],
            'errors' => $errors,
        ];
    }

    private function normalizeHeader(array $row): array
    {
        return collect($row)->map(function ($v) {
            $v = (string) $v;
            $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);
            $v = strtolower(trim($v));
            $v = str_replace([' ', '-', '__'], ['_', '_', '_'], $v);
            $v = preg_replace('/[^a-z0-9_]/', '', $v);
            return $v;
        })->filter()->values()->all();
    }

    private function rowToAssoc(array $header, array $row): array
    {
        $out = [];
        foreach ($header as $i => $k) {
            $out[$k] = isset($row[$i]) ? (string) $row[$i] : '';
        }
        return $out;
    }

    private function isEmptyRow(array $assoc): bool
    {
        foreach ($assoc as $k => $v) {
            if (trim((string) $v) !== '') {
                return false;
            }
        }
        return true;
    }

    private function normalizeRow(array $r): array
    {
        $r = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $r);
        $r['email'] = strtolower(trim((string) ($r['email'] ?? '')));
        $r['phone'] = preg_replace('/\D+/', '', (string) ($r['phone'] ?? ''));
        $r['gender'] = strtolower(trim((string) ($r['gender'] ?? '')));
        $r['name'] = trim((string) ($r['name'] ?? ''));
        $r['address'] = trim((string) ($r['address'] ?? ''));
        $r['coupon_code'] = trim((string) ($r['coupon_code'] ?? ''));

        foreach (['city', 'province', 'postal_code', 'emergency_contact_name', 'emergency_contact_number', 'date_of_birth', 'target_time', 'jersey_size', 'bib_number'] as $k) {
            if (! array_key_exists($k, $r)) {
                $r[$k] = '';
            } else {
                $r[$k] = trim((string) $r[$k]);
            }
        }

        if ($r['date_of_birth'] !== '') {
            try {
                $r['date_of_birth'] = Carbon::parse($r['date_of_birth'])->format('Y-m-d');
            } catch (\Throwable $e) {
                $r['date_of_birth'] = '';
            }
        }

        return $r;
    }

    private function err(int $rowNumber, string $message): array
    {
        return ['row' => $rowNumber, 'message' => $message];
    }

    private function getCategoryPrice(RaceCategory $category): array
    {
        $now = now();
        $early = (int) ($category->price_early ?? 0);
        $regular = (int) ($category->price_regular ?? 0);
        $late = (int) ($category->price_late ?? 0);

        if ($early > 0) {
            $isEarlyValid = true;

            if ($category->early_bird_end_at && $now->greaterThan($category->early_bird_end_at)) {
                $isEarlyValid = false;
            }

            if ($isEarlyValid && $category->early_bird_quota) {
                $earlySold = Participant::where('race_category_id', $category->id)
                    ->where('price_type', 'early')
                    ->whereHas('transaction', function ($q) {
                        $q->whereIn('payment_status', ['pending', 'paid', 'cod']);
                    })
                    ->count();

                if ($earlySold >= $category->early_bird_quota) {
                    $isEarlyValid = false;
                }
            }

            if ($isEarlyValid) {
                return ['price' => $early, 'type' => 'early'];
            }
        }

        if ($late > 0 && $regular === 0) {
            return ['price' => $late, 'type' => 'late'];
        }

        return ['price' => $regular, 'type' => 'regular'];
    }
}
