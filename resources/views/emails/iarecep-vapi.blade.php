<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:24px;">
        <h2 style="margin-top:0;">IA DIAL — Notification Vapi</h2>
        <table style="width:100%;border-collapse:collapse;">
            @foreach ($rows as $label => $value)
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;width:180px;vertical-align:top;">
                        {{ $label }}
                    </td>
                    <td style="padding:8px;border-bottom:1px solid #eee;white-space:pre-line;">
                        {{ $value }}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>