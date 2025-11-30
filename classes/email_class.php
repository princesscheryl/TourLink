<?php
/**
 * Email Class for TourLink Platform
 * Handles sending emails for notifications, tickets, invoices, etc.
 */
class Email {
    private $from_email = 'noreply@tourlink.com.gh';
    private $from_name = 'TourLink Support';

    /**
     * Send email using PHP mail function
     * For production, consider using PHPMailer or similar
     */
    public function send_email($to_email, $subject, $body, $is_html = true) {
        if (empty($to_email)) {
            return false;
        }

        $headers = [];
        $headers[] = "From: {$this->from_name} <{$this->from_email}>";
        $headers[] = "Reply-To: {$this->from_email}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        if ($is_html) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        $headers_string = implode("\r\n", $headers);

        // Wrap body in HTML template if HTML email
        if ($is_html) {
            $body = $this->wrap_in_template($body);
        }

        return mail($to_email, $subject, $body, $headers_string);
    }

    /**
     * Wrap email body in HTML template
     */
    private function wrap_in_template($body) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .email-header { background: #2d6a4f; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .email-body { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .email-footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h2>TourLink</h2>
                </div>
                <div class='email-body'>
                    {$body}
                </div>
                <div class='email-footer'>
                    <p>This is an automated email from TourLink. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " TourLink. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

