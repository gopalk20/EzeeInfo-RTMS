<?php

namespace App\Libraries;

/**
 * Sends approval/rejection emails for timesheet entries.
 * Uses Gmail SMTP when configured (Admin → Email Settings).
 */
class EmailService
{
    protected ?\CodeIgniter\Email\Email $email = null;
    protected array $config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig(): void
    {
        $path = WRITEPATH . 'config/email.json';
        if (is_file($path)) {
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            if (is_array($data)) {
                $this->config = $data;
                return;
            }
        }
        $this->config = [
            'protocol'  => env('email.protocol', 'smtp'),
            'SMTPHost'  => env('email.SMTPHost', ''),
            'SMTPUser'  => env('email.SMTPUser', ''),
            'SMTPPass'  => env('email.SMTPPass', ''),
            'SMTPPort'  => (int) (env('email.SMTPPort') ?: 587),
            'SMTPCrypto'=> env('email.SMTPCrypto', 'tls'),
            'fromEmail' => env('email.fromEmail', ''),
            'fromName'  => env('email.fromName', 'RTMS'),
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['SMTPHost']) && !empty($this->config['SMTPUser']) && !empty($this->config['SMTPPass']);
    }

    /**
     * Send test email (for "Test connection").
     */
    public function sendTestEmail(string $to): bool
    {
        return $this->send($to, 'RTMS Test – SMTP OK', 'This is a test email from RTMS. Your Gmail SMTP configuration is working.');
    }

    /**
     * Send approval email to employee.
     */
    public function sendApprovalEmail(string $toEmail, string $employeeName, string $taskTitle, string $workDate, float $hours): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }
        $subject = 'Timesheet Approved – ' . $taskTitle;
        $body = "Hello " . ($employeeName ?: 'there') . ",\n\n" .
            "Your timesheet entry has been approved.\n\n" .
            "Task: {$taskTitle}\n" .
            "Date: {$workDate}\n" .
            "Hours: {$hours}\n\n" .
            "Thank you.\n";
        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Send rejection email to employee.
     */
    public function sendRejectionEmail(string $toEmail, string $employeeName, string $taskTitle, string $workDate, float $hours, ?string $feedback = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }
        $subject = 'Timesheet Rejected – ' . $taskTitle;
        $body = "Hello " . ($employeeName ?: 'there') . ",\n\n" .
            "Your timesheet entry has been rejected.\n\n" .
            "Task: {$taskTitle}\n" .
            "Date: {$workDate}\n" .
            "Hours: {$hours}\n";
        if ($feedback) {
            $body .= "\nFeedback: {$feedback}\n";
        }
        $body .= "\nPlease review and resubmit if needed.\n";
        return $this->send($toEmail, $subject, $body);
    }

    protected function send(string $to, string $subject, string $body): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }
        $email = \Config\Services::email();
        $email->clear();
        $email->initialize([
            'protocol'   => $this->config['protocol'] ?? 'smtp',
            'SMTPHost'   => $this->config['SMTPHost'] ?? '',
            'SMTPUser'   => $this->config['SMTPUser'] ?? '',
            'SMTPPass'   => $this->config['SMTPPass'] ?? '',
            'SMTPPort'   => $this->config['SMTPPort'] ?? 587,
            'SMTPCrypto' => $this->config['SMTPCrypto'] ?? 'tls',
            'mailType'   => 'text',
        ]);
        $from = $this->config['fromEmail'] ?: $this->config['SMTPUser'];
        $fromName = $this->config['fromName'] ?: 'RTMS';
        $email->setFrom($from, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($body);
        $result = $email->send(false);
        if (!$result) {
            log_message('error', 'Email send failed: ' . ($email->printDebugger(['headers']) ?: 'unknown'));
        }
        return $result;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function saveConfig(array $config): bool
    {
        $dir = WRITEPATH . 'config';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/email.json';
        $data = [
            'protocol'  => $config['protocol'] ?? 'smtp',
            'SMTPHost'  => trim($config['SMTPHost'] ?? ''),
            'SMTPUser'  => trim($config['SMTPUser'] ?? ''),
            'SMTPPass'  => trim($config['SMTPPass'] ?? ''),
            'SMTPPort'  => (int) ($config['SMTPPort'] ?? 587),
            'SMTPCrypto'=> $config['SMTPCrypto'] ?? 'tls',
            'fromEmail' => trim($config['fromEmail'] ?? ''),
            'fromName'  => trim($config['fromName'] ?? 'RTMS'),
        ];
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }
}
