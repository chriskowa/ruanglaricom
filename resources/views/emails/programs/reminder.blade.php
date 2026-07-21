<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Program Latihan - RuangLari</title>
</head>
<body style="margin: 0; padding: 0; background-color: #030712; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #f3f4f6; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #030712; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #0b0f19; border: 1px solid #1f2937; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <span style="font-size: 32px; font-weight: 900; color: #ccff00; letter-spacing: -1px; font-style: italic;">RUANGLARI</span>
                            <div style="height: 1px; background: linear-gradient(90deg, transparent, #ccff00, transparent); margin-top: 15px; width: 100%;"></div>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <h2 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 0; text-transform: uppercase; letter-spacing: -0.5px; font-style: italic;">Pengingat Sesi Latihan</h2>
                            <p style="font-size: 14px; color: #9ca3af; line-height: 1.6;">
                                Halo <strong>{{ $runner->name }}</strong>,
                            </p>
                            
                            @if($customMessage)
                                <div style="background-color: #030712; border-left: 4px solid #ccff00; border-radius: 0 16px 16px 0; padding: 16px; margin: 20px 0; font-size: 14px; color: #ffffff; line-height: 1.6; font-style: italic;">
                                    "{!! nl2br(e($customMessage)) !!}"
                                </div>
                            @else
                                <p style="font-size: 14px; color: #9ca3af; line-height: 1.6;">
                                    Berikut adalah jadwal sesi latihan Anda untuk program <strong>{{ $program->title }}</strong>:
                                </p>
                            @endif
                            
                            <!-- Session Details Box -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #030712; border: 1px solid #1f2937; border-radius: 16px; margin: 24px 0; padding: 20px;">
                                <tr>
                                    <td style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-bottom: 5px;">Tipe Sesi</td>
                                    <td align="right" style="font-size: 14px; color: #ffffff; font-weight: bold; text-transform: uppercase;">
                                        {{ ucfirst(str_replace('_', ' ', $sessionData['type'] ?? 'Rest')) }}
                                    </td>
                                </tr>
                                
                                @if(!empty($sessionData['distance']))
                                <tr>
                                    <td style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-top: 10px; padding-bottom: 5px; border-top: 1px solid #1f2937;">Target Jarak</td>
                                    <td align="right" style="font-size: 14px; color: #ccff00; font-weight: bold; padding-top: 10px;">
                                        {{ $sessionData['distance'] }} km
                                    </td>
                                </tr>
                                @endif

                                @if(!empty($sessionData['duration']))
                                <tr>
                                    <td style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-top: 10px; padding-bottom: 5px; border-top: 1px solid #1f2937;">Durasi</td>
                                    <td align="right" style="font-size: 14px; color: #ffffff; font-weight: bold; padding-top: 10px;">
                                        {{ $sessionData['duration'] }}
                                    </td>
                                </tr>
                                @endif

                                @if(!empty($sessionData['target_pace']))
                                <tr>
                                    <td style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-top: 10px; padding-bottom: 5px; border-top: 1px solid #1f2937;">Target Pace</td>
                                    <td align="right" style="font-size: 14px; color: #ccff00; font-weight: bold; padding-top: 10px; font-family: monospace;">
                                        {{ $sessionData['target_pace'] }}
                                    </td>
                                </tr>
                                @endif

                                @if(!empty($sessionData['description']) || !empty($sessionData['notes']) || !empty($sessionData['instruction']))
                                <tr>
                                    <td colspan="2" style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-top: 15px; border-top: 1px solid #1f2937; margin-top: 15px; padding-bottom: 5px;">
                                        Instruksi Latihan
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="font-size: 14px; color: #ffffff; line-height: 1.5; padding-top: 5px;">
                                        {{ $sessionData['description'] ?? $sessionData['notes'] ?? $sessionData['instruction'] ?? '-' }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <a href="{{ route('runner.calendar') }}" style="display: inline-block; background-color: #ccff00; color: #030712; font-size: 14px; font-weight: bold; text-decoration: none; padding: 16px 32px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 14px 0 rgba(204, 255, 0, 0.3);">
                                            Lihat Kalender Latihan
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 13px; color: #6b7280; line-height: 1.6; text-align: center;">
                                Tetap konsisten dan jaga kesehatan Anda. Sampai jumpa di lintasan lari!
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 40px 40px 40px; background-color: #030712; border-top: 1px solid #1f2937;">
                            <p style="font-size: 11px; color: #4b5563; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                                &copy; {{ date('Y') }} RuangLari. All Rights Reserved.
                            </p>
                            <p style="font-size: 11px; color: #4b5563; margin-top: 5px;">
                                Email ini dikirim secara otomatis oleh sistem RuangLari.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
