<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Sonata\AdminBundle\Form\Type\AdminType;

class UserAdmin extends AbstractAdmin
{
    protected $formOptions = array(
        'validation_groups' => 'Profile'
    );

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('username', null, array('label' => 'Login'))
            ->add('oldUsernames', null, array('label' => 'Autres logins'))
            ->add('email', null, array('label' => 'Email'))
            ->add('enabled', null, array('editable' => true, 'label' => 'Activé'))
            ->add('locked', null, array('editable' => true, 'label' => 'Verrouillé'))
            ->add('createdAt', null, array('label' => 'Créé le'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('username', null, array('label' => 'Login'))
            ->add('locked', null, array('label' => 'Verrouillé'))
            ->add('email', null, array('label' => 'Email'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
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

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('General')
                ->add('username', TextType::class, array(
                    'label' => 'Login'
                ))
                ->add('oldUsernames', CollectionType::class, array(
                    'required' => false,
                    'entry_type'   => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => 'Autres logins'
                ))
                ->add('email', EmailType::class, array(
                    'label' => 'Email'
                ))
                ->add('receiveNewsletter', ChoiceType::class, array(
                    'label' => 'Newsletter',
                    'multiple' => false,
                    'expanded' => true,
                    'choices'  => array('Oui' => true, 'Non' => false),
                    'help' => 'L\'utilisateur accepte de recevoir des newsletter'
                ))
                ->add('receiveAvertissement', ChoiceType::class, array(
                    'label' => 'Avertissements',
                    'multiple' => false,
                    'expanded' => true,
                    'choices'  => array('Oui' => true, 'Non' => false),
                    'help' => 'L\'utilisateur accepte de recevoir des avertissements par mail à chaque nouveau message qu\'il reçoit'
                ))
                ->add('plainPassword', TextType::class, array(
                    'required' => false,
                    'label' => 'Mot de passe'
                ))
                ->add('dateOfBirth', BirthdayType::class, array(
                    'required' => false,
                    'label' => 'Date de naissance'
                 ))
                ->add('description', TextareaType::class, array(
                    'required' => false,
                    'label' => 'Description',
                    'attr' => array(
                        'class' => 'tinymce',
                        'tinymce'=>'{"theme":"simple"}'
                    )
                ))
                ->add('gender', ChoiceType::class, array(
                    'choices' => array('male' => 'm', 'female' => 'f'),
                    'required' => false,
                    'translation_domain' => $this->getTranslationDomain(),
                    'label' => 'Sexe'
                ))
                ->add('locale', LocaleType::class, array(
                    'required' => false,
                    'label' => 'Langue'
                 ))
                ->add('timezone', TimezoneType::class, array(
                    'required' => false,
                    'label' => 'Fuseau horaire'
                 ))
            ->end()
            ->with('Ninja')
                ->add('ninja', AdminType::class, array('label' => false), array('edit' => 'inline'))
            ->end()
        ;

        $formMapper
            ->with('Sécurité')
                ->add('confirmationToken', TextType::class, array('required' => false))
                ->add('autologin', TextType::class, array('required' => false))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(object $user): void
    {
        if(!isset($this->userManager))
            return;
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(object $object=null): void
    {
        if(!isset($this->userManager))
            return;
        $em = $this->userManager->getManager();
        $conn = $em->getConnection();
        $evm = $em->getEventManager();

        // enlève les évènement sur clan_proposition
        // on évite d'envoyer des messages qui seront supprimés
        $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_clan.clan_proposition_listener'));

        // enlève les évènement sur thread et comment
        // tout sera remis à plat à la fin
        $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_forum.thread_listener'));
        $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_forum.comment_listener'));
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(object $object=null): void
    {
        $conn = $this->modelManager->getEntityManager()->getConnection();

        // recalcul les nombres de réponses d'un thread
        $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT COUNT(nt_comment.id) as num, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.num_comments = isnull(c.num, 0)");
        // recalcul les nombres de réponses d'un forum
        $conn->executeUpdate("UPDATE nt_forum as f LEFT JOIN (SELECT COUNT(nt_thread.id) as num, forum_id FROM nt_thread GROUP BY forum_id) t ON t.forum_id=f.id SET f.num_threads = isnull(t.num, 0)");

        // ré-affecte les derniers commentaires
        $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT MAX(date_ajout) as lastAt, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.last_comment_at = c.lastAt");
        $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT author_id as lastBy, thread_id, date_ajout FROM nt_comment as ct) c ON c.thread_id=t.id and c.date_ajout=t.last_comment_at SET t.lastCommentBy_id = c.lastBy");
        $conn->executeUpdate("UPDATE nt_thread as t SET t.last_comment_at=t.date_ajout WHERE t.last_comment_at IS NULL");
    }

    /**
    * {@inheritdoc}
    */
    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            'Utilisateur',
            $admin->generateMenuUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            'Détection multi-compte par ip',
            $admin->generateMenuUrl('admin.detection.list', array('id' => $id))
        );

        $menu->addChild(
            'Messages - messagerie',
            $admin->generateMenuUrl('admin.message.list', array('id' => $id))
        );

        $menu->addChild(
            'Commentaires - forum',
            $admin->generateMenuUrl('admin.comment_user.list', array('id' => $id))
        );

        $menu->addChild(
            'Amis',
            $admin->generateMenuUrl('admin.friend.list', array('id' => $id))
        );

        $menu->addChild(
            'Captures',
            $admin->generateMenuUrl('admin.capture.list', array('id' => $id))
        );

        $menu->addChild(
            'Recrutements',
            $admin->generateMenuUrl('admin.clan_proposition.list', array('id' => $id))
        );

    }
}
