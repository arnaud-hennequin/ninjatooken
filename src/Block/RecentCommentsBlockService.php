<?php

namespace App\Block;

use App\Entity\Forum\Comment;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\DoctrineORMAdminBundle\Datagrid\Pager;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class RecentCommentsBlockService extends AbstractBlockService
{
    private EntityManager $em;

    public function __construct(Environment $twig, EntityManager $entityManager)
    {
        $this->em = $entityManager;

        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $query = $this->em->getRepository(Comment::class)
            ->createQueryBuilder('c')
            ->orderby('c.dateAjout', 'DESC');
        $pager = new Pager();
        $pager->setMaxPerPage($blockContext->getSetting('number'));
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage(1);
        $pager->init();

        $parameters = [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'pager' => $pager,
        ];

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }

    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['number', 'integer', ['required' => true]],
                ['title', 'text', ['required' => false]],
                ['mode', 'choice', [
                    'choices' => [
                        'public' => 'public',
                        'admin' => 'admin',
                    ],
                ]],
            ],
        ]);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'number' => 5,
            'mode' => 'public',
            'title' => 'Recent Comments',
            'template' => 'forum/block/recent_comments.html.twig',
        ]);
    }
}
