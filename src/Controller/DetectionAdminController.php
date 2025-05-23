<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DetectionAdminController extends Controller
{
    public function list(EntityManagerInterface $em, ?Request $request = null): Response
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        $showForm = true;

        $users = [];
        if ($this->admin->isChild()) {
            $user = $this->admin->getParent()->getObject($request->get($this->admin->getParent()->getIdParameter()));
            $users = $userRepository->getMultiAccountByUser($user);
            $showForm = false;
        } else {
            if (Request::METHOD_POST == $request->getMethod()) {
                $ip = $request->get('ip');
                if (!empty($ip)) {
                    $ip = ip2long($ip);
                }

                $username = $request->get('username');

                $users = $userRepository->getMultiAccount($ip, $username);
            }
        }

        return $this->render('user/detectionAdmin/detection.html.twig', [
            'action' => 'list',
            'locale' => $request->getLocale(),
            'users' => $users,
            'showForm' => $showForm,
        ]);
    }
}
