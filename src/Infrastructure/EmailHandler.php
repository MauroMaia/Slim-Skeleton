<?php

namespace App\Infrastructure;

use App\Domain\User\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailHandler
{
    private PHPMailer $mail;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        //Create an instance; passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        //Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;                                 //Enable verbose debug output
        $this->mail->isSMTP();                                                    //Send using SMTP
        $this->mail->Host = MAIL_SERVER;                                          //Set the SMTP server to send through
        $this->mail->SMTPAuth = true;                                             //Enable SMTP authentication
        $this->mail->Username = MAIL_FROM;                                        //SMTP username
        $this->mail->Password = MAIL_FROM_PASSWORD;                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;                 //Enable implicit TLS encryption
        $this->mail->Port = MAIL_PORT;                                            //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mail->setFrom(MAIL_FROM, 'no-reply');
        $this->mail->addReplyTo(MAIL_FROM, 'no-reply');
    }

    private function withHTMLBody(string $emailContent): void
    {
        $this->mail->isHTML();
        $this->mail->Body = $emailContent;
        $this->mail->AltBody = str_replace("|a", "<a", strip_tags(str_replace("<a", "|a", $emailContent)));
    }

    /**
     * @throws Exception
     */
    private function sendSingleUser(User $to, $subject): void
    {
        $this->mail->Subject = $subject;
        $this->mail->addAddress($to->email, $to->getFirstName() . ' ' . $to->getLastName());
        $this->mail->send();
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public static function SendRecoverEmail(User $to, string $url, Environment $twig): void
    {
        $handler = new EmailHandler();
        $handler->withHTMLBody($twig->render('login/reset-password-email.twig', ['url' => $url]));
        try {
            $handler->sendSingleUser($to, 'Slim -skeleton | Recover Password');
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function SendWelcomeEmail(User $to, string $url, Environment $twig): void
    {
        $handler = new EmailHandler();
        $handler->withHTMLBody($twig->render('welcome-email.twig', ['url' => $url]));

        try {
            $handler->sendSingleUser($to, 'Slim -skeleton | Welcome');
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function SendVerifyEmail(User $to, string $url, Environment $twig): void
    {
        $handler = new EmailHandler();
        $handler->withHTMLBody($twig->render('verify-email.twig', ['url' => $url]));

        try {
            $handler->sendSingleUser($to, 'Slim -skeleton | Verify');
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }
}
