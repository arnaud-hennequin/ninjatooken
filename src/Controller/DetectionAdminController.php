<?php

namespace App\Controller;

use App\Admin\DetectionAdmin;
use App\Entity\User\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends Controller<DetectionAdmin>
 */
class DetectionAdminController extends Controller
{
    public function list(EntityManagerInterface $em, ?Request $request = null): Response
    {
        if (false === $this->admin->isGranted('LIST') || $request === null) {
            throw new AccessDeniedException();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        $showForm = true;

        $users = [];
        if ($this->admin->isChild()) {
            /** @var ?User $user */
            $user = $this->admin->getParent()->getObject($request->get($this->admin->getParent()->getIdParameter()));
            $users = $userRepository->getMultiAccountByUser($user);
            $showForm = false;
        } elseif (Request::METHOD_POST === $request->getMethod()) {
            $ip = $request->get('ip');
            if (!empty($ip)) {
                $ip = ip2long((string) $ip);
                if ($ip === false) {
                    $ip = null;
                } else {
                    $ip = (string) $ip;
                }
            } else {
                $ip = null;
            }

            $username = $request->get('username');

            $users = $userRepository->getMultiAccount($ip, $username);
        }

        return $this->render('user/detectionAdmin/detection.html.twig', [
            'action' => 'list',
            'locale' => $request->getLocale(),
            'users' => $users,
            'showForm' => $showForm,
        ]);
    }
}
