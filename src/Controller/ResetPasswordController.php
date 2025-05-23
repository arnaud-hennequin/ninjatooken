<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route(path: '/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Display & process form to request a password reset.
     */
    public function request(Request $request, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $userRepository
            );
        }

        return $this->render('user/resetting/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('ninja_tooken_user_resetting_request');
        }

        return $this->render('user/resetting/checkEmail.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, ?string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('ninja_tooken_user_resetting_reset');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('ninja_tooken_user_resetting_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('ninja_tooken_user_profile_show');
        }

        return $this->render('user/resetting/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, UserRepository $userRepository): RedirectResponse
    {
        $user = $userRepository->findOneBy([
            'email' => $emailFormData,
        ]);
        if (!$user) {
            $user = $userRepository->findOneBy([
                'username' => $emailFormData,
            ]);
        }

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            $this->addFlash('warning', 'Aucun utilisateur correspondant trouvé.');

            return $this->redirectToRoute('ninja_tooken_user_resetting_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            $this->addFlash('reset_password_error', sprintf(
                'Une erreur est survenue lors de la réinitialisation de ton mot-de-passe : %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('ninja_tooken_user_resetting_check_email');
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@ninjatooken.fr', 'NinjaTooken'))
                ->to($user->getEmail())
                ->subject('Your password reset request')
                ->htmlTemplate('user/resetting/email.html.twig')
                ->context([
                    'resetToken' => $resetToken,
                    'locale' => $user->getLocale() ?? 'fr',
                ])
            ;

            $mailer->send($email);
        } catch (\Exception|TransportExceptionInterface $e) {
            $this->addFlash('warning', 'Une erreur est survenue lors de l\'envoi du mail : '.$e->getMessage());

            return $this->redirectToRoute('ninja_tooken_user_resetting_check_email');
        }

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('ninja_tooken_user_resetting_check_email');
    }
}
