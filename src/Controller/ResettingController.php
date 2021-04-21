<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller managing the resetting of the password.
 *
 * @final
 */
class ResettingController extends AbstractController
{
    /**
     * @var int
     */
    private $retryTtl = 600;

    /**
     * Request reset user password: show form.
     */
    public function request()
    {
        return $this->render('user/resetting/request.html.twig');
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @return Response
     */
    public function sendEmail(Request $request, UserManagerInterface $userManager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer)
    {
        $username = $request->request->get('username');

        $user = $userManager->findUserByUsernameOrEmail($username);

        if (null !== $user && !$user->isPasswordRequestNonExpired($this->retryTtl)) {

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            $mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $userManager->updateUser($user);
        }

        return new RedirectResponse($this->generateUrl('ninja_tooken_user_resetting_check_email', ['username' => $username]));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @return Response
     */
    public function checkEmail(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('ninja_tooken_user_resetting_request'));
        }

        return $this->render('user/resetting/check_email.html.twig', [
            'tokenLifetime' => ceil($this->retryTtl / 3600),
        ]);
    }

    /**
     * Reset user password.
     *
     * @param string $token
     *
     * @return Response
     */
    public function reset(Request $request, $token, FactoryInterface $formFactory, UserManagerInterface $userManager)
    {
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('ninja_tooken_user_security_login'));
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userManager->updateUser($user);
            $url = $this->generateUrl('ninja_tooken_user_profile_show');

            return new RedirectResponse($url);
        }

        return $this->render('user/resetting/reset.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }
}
