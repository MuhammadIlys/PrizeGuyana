<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e5e5e5; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #0a0a0a; padding: 40px 10px;">
        <tr>
            <td align="center">
                
                <!-- Main Card Container -->
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 480px; background-color: #121212; border: 1px solid #262626; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
                    
                    <!-- Top Amber Accent Bar -->
                    <tr>
                        <td style="height: 4px; background: linear-gradient(90deg, #f59e0b, #d97706);"></td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 32px; text-align: center;">
                            
                            <!-- Key/Lock Icon -->
                            <div style="display: inline-block; width: 56px; height: 56px; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 16px; line-height: 56px; margin-bottom: 24px;">
                                <span style="font-size: 24px; color: #fbbf24;">🔑</span>
                            </div>

                            <!-- Header -->
                            <h1 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                                Reset Your Password
                            </h1>

                            <p style="margin: 0 0 28px 0; font-size: 14px; line-height: 1.6; color: #a3a3a3;">
                                Hello, we received a request to reset the password for your account associated with <strong style="color: #e5e5e5;">{{ $user->email }}</strong>.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto 28px auto;">
                                <tr>
                                    <td align="center" style="border-radius: 12px; background: linear-gradient(90deg, #f59e0b, #d97706);">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 13px; font-weight: 800; color: #000000; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 12px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px 0; font-size: 12px; color: #737373; line-height: 1.5;">
                                This password reset link will expire in <strong>{{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes</strong>.<br>
                                If you did not request a password reset, no further action is required.
                            </p>

                            <!-- Fallback Link Section -->
                            <div style="border-t: 1px solid #262626; padding-top: 20px; text-align: left;">
                                <p style="margin: 0 0 8px 0; font-size: 11px; color: #737373;">
                                    If you are having trouble clicking the button, copy and paste the URL below into your browser:
                                </p>
                                <a href="{{ $url }}" style="font-size: 11px; color: #fbbf24; word-break: break-all; text-decoration: none;">
                                    {{ $url }}
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 16px 32px; background-color: #171717; border-top: 1px solid #262626; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #525252;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>