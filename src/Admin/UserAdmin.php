<?php

namespace App\Admin;

use App\Entity\User\User;
use App\Listener\ClanPropositionListener;
use App\Listener\CommentListener;
use App\Listener\ThreadListener;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;

/**
 * @extends AbstractAdmin<User>
 */
class UserAdmin extends AbstractAdmin
{
    protected ClanPropositionListener $clanPropositionListener;
    protected ThreadListener $threadListener;
    protected CommentListener $commentListener;
    protected EntityManagerInterface $entityManager;

    public function __construct(string $code, string $class, string $baseControllerName, ClanPropositionListener $clanPropositionListener, ThreadListener $threadListener, CommentListener $commentListener, EntityManagerInterface $entityManager)
    {
        $this->clanPropositionListener = $clanPropositionListener;
        $this->threadListener = $threadListener;
        $this->commentListener = $commentListener;
        $this->entityManager = $entityManager;

        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('username', null, ['label' => 'Login'])
            ->add('oldUsernames', null, ['label' => 'Autres logins'])
            ->add('email', null, ['label' => 'Email'])
            ->add('enabled', null, ['editable' => true, 'label' => 'Activé'])
            ->add('locked', null, ['editable' => true, 'label' => 'Verrouillé'])
            ->add('createdAt', null, ['label' => 'Créé le'])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('username', null, ['label' => 'Login'])
            ->add('locked', null, ['label' => 'Verrouillé'])
            ->add('email', null, ['label' => 'Email'])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('General')
                ->add('username')
                ->add('oldUsernames')
                ->add('email')
                ->add('dateOfBirth')
                ->add('biography')
                ->add('gender')
                ->add('locale')
                ->add('timezone')
            ->end()
            ->with('Ninja')
                ->add('ninja')
            ->end()
            ->with('Security')
                ->add('confirmationToken')
                ->add('autologin')
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General')
                ->add('username', TextType::class, [
                    'label' => 'Login',
                ])
                ->add('oldUsernames', CollectionType::class, [
                    'required' => false,
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => 'Autres logins',
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                ])
                ->add('receiveNewsletter', ChoiceType::class, [
                    'label' => 'Newsletter',
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => ['Oui' => true, 'Non' => false],
                    'help' => 'L\'utilisateur accepte de recevoir des newsletter',
                ])
                ->add('receiveAvertissement', ChoiceType::class, [
                    'label' => 'Avertissements',
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => ['Oui' => true, 'Non' => false],
                    'help' => 'L\'utilisateur accepte de recevoir des avertissements par mail à chaque nouveau message qu\'il reçoit',
                ])
                ->add('plainPassword', TextType::class, [
                    'required' => false,
                    'label' => 'Mot de passe',
                ])
                ->add('dateOfBirth', BirthdayType::class, [
                    'required' => false,
                    'label' => 'Date de naissance',
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'label' => 'Description',
                    'attr' => [
                        'class' => 'tinymce',
                        'tinymce' => '{"theme":"simple"}',
                    ],
                ])
                ->add('gender', ChoiceType::class, [
                    'choices' => ['male' => 'm', 'female' => 'f'],
                    'required' => false,
                    'translation_domain' => $this->getTranslationDomain(),
                    'label' => 'Sexe',
                ])
                ->add('locale', LocaleType::class, [
                    'required' => false,
                    'label' => 'Langue',
                ])
                ->add('timezone', TimezoneType::class, [
                    'required' => false,
                    'label' => 'Fuseau horaire',
                ])
            ->end()
            ->with('Ninja')
                ->add('ninja', AdminType::class, ['label' => false], ['edit' => 'inline'])
            ->end()
        ;

        $form
            ->with('Sécurité')
                ->add('confirmationToken', TextType::class, ['required' => false])
                ->add('autologin', TextType::class, ['required' => false])
            ->end()
        ;
    }

    public function preRemove(?object $object = null): void
    {
        // enlève les évènement sur clan_proposition
        // on évite d'envoyer des messages qui seront supprimés
        $this->entityManager->getEventManager()->removeEventListener(['postRemove'], $this->clanPropositionListener);

        // enlève les évènement sur thread et comment
        // tout sera remis à plat à la fin
        $this->entityManager->getEventManager()->removeEventListener(['postRemove'], $this->threadListener);
        $this->entityManager->getEventManager()->removeEventListener(['postRemove'], $this->commentListener);
    }

    public function postRemove(?object $object = null): void
    {
        $conn = $this->entityManager->getConnection();

        // recalcul les nombres de réponses d'un thread
        $conn->executeUpdate('UPDATE nt_thread as t LEFT JOIN (SELECT COUNT(nt_comment.id) as num, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.num_comments = isnull(c.num, 0)');
        // recalcul les nombres de réponses d'un forum
        $conn->executeUpdate('UPDATE nt_forum as f LEFT JOIN (SELECT COUNT(nt_thread.id) as num, forum_id FROM nt_thread GROUP BY forum_id) t ON t.forum_id=f.id SET f.num_threads = isnull(t.num, 0)');

        // ré-affecte les derniers commentaires
        $conn->executeUpdate('UPDATE nt_thread as t LEFT JOIN (SELECT MAX(date_ajout) as lastAt, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.last_comment_at = c.lastAt');
        $conn->executeUpdate('UPDATE nt_thread as t LEFT JOIN (SELECT author_id as lastBy, thread_id, date_ajout FROM nt_comment as ct) c ON c.thread_id=t.id and c.date_ajout=t.last_comment_at SET t.lastCommentBy_id = c.lastBy');
        $conn->executeUpdate('UPDATE nt_thread as t SET t.last_comment_at=t.date_ajout WHERE t.last_comment_at IS NULL');
    }

    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            'Utilisateur',
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild(
            'Détection multi-compte par ip',
            $admin->generateMenuUrl('admin.detection.list', ['id' => $id])
        );

        $menu->addChild(
            'Messages - messagerie',
            $admin->generateMenuUrl('admin.message.list', ['id' => $id])
        );

        $menu->addChild(
            'Commentaires - forum',
            $admin->generateMenuUrl('admin.comment_user.list', ['id' => $id])
        );

        $menu->addChild(
            'Amis',
            $admin->generateMenuUrl('admin.friend.list', ['id' => $id])
        );

        $menu->addChild(
            'Captures',
            $admin->generateMenuUrl('admin.capture.list', ['id' => $id])
        );

        $menu->addChild(
            'Recrutements',
            $admin->generateMenuUrl('admin.clan_proposition.list', ['id' => $id])
        );
    }
}
