<?php

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User\User;

class DetectionAdminController extends Controller
{

    public function list(Request $request = null)
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository(User::class);

        $showForm = true;

        $users = array();
        if ($this->admin->isChild()){
            $user = $this->admin->getParent()->getObject($request->get($this->admin->getParent()->getIdParameter()));
            $users = $userRepository->getMultiAccountByUser($user);
            $showForm = false;
        }else{
            if ($this->getRestMethod() == 'POST') {

                $ip = $request->get('ip');
                if(!empty($ip))
                    $ip = ip2long($ip);

                $username = $request->get('username');

                $users = $userRepository->getMultiAccount($ip, $username);
            }
        }

        return $this->render('user/detectionAdmin/detection.html.twig', array(
            'action'     => 'list',
            'locale' => $request->getLocale(),
            'users' => $users,
            'showForm' => $showForm
        ));
    }
}