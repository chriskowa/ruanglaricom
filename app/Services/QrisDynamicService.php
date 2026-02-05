<?php

namespace App\Services;

class QrisDynamicService
{
    public function generate(string $qrisStatic, int $nominal, ?string $nmid = null): string
    {
        $qrisStatic = trim($qrisStatic);
        if ($qrisStatic === '') {
            throw new \InvalidArgumentException('QRIS static wajib diisi.');
        }
        if ($nominal <= 0) {
            throw new \InvalidArgumentException('Nominal tidak valid.');
        }

        $qris = substr($qrisStatic, 0, -4);

        if (str_contains($qris, '010211')) {
            $qris = str_replace('010211', '010212', $qris);
        }

        $nmid = $nmid ? trim($nmid) : null;
        if ($nmid) {
            $nmidTag = '51'.$this->pad(strlen($nmid)).$nmid;
            if (str_contains($qris, '5204')) {
                $qris = str_replace('5204', $nmidTag.'5204', $qris);
            } else {
                $qris = str_replace('5303', $nmidTag.'5303', $qris);
            }
        }

        $nominalString = (string) $nominal;
        $amountTag = '54'.$this->pad(strlen($nominalString)).$nominalString;
        if (str_contains($qris, '5802ID')) {
            $qris = str_replace('5802ID', $amountTag.'5802ID', $qris);
        }

        $qris .= '6304';
        $qris .= $this->toCRC16($qris);

        return $qris;
    }

    private function pad(int $number): string
    {
        return $number < 10 ? '0'.$number : (string) $number;
    }

    private function toCRC16(string $input): string
    {
        $crc = 0xffff;
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($input[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
            }
        }

        $hex = strtoupper(dechex($crc & 0xffff));
        return strlen($hex) === 3 ? '0'.$hex : str_pad($hex, 4, '0', STR_PAD_LEFT);
    }
}

