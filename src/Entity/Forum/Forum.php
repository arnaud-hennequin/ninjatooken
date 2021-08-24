<?php

namespace App\Entity\Forum;

use App\Entity\Clan\Clan;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;

/**
 * Forum
 *
 * @ORM\Table(name="nt_forum")
 * @ORM\Entity(repositoryClass="App\Repository\ForumRepository")
 */
class Forum implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="smallint")
     */
    private $ordre = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_user_create_thread", type="boolean")
     */
    private $canUserCreateThread = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="num_threads", type="integer")
     */
    private $numThreads = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clan\Clan", inversedBy="forums", fetch="EAGER")
     */
    private $clan;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
    }

    public function __toString(){
        return $this->nom;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['nom'];
    }

    public function generateSlugValue($values): string
    {
        $usableValues = [];
        foreach ($values as $fieldValue) {
            if (! empty($fieldValue)) {
                $usableValues[] = $fieldValue;
            }
        }

        $this->ensureAtLeastOneUsableValue($values, $usableValues);

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $unicodeString = (new \Symfony\Component\String\Slugger\AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        $slug = strtolower($unicodeString->toString());

        if (empty($slug)) {
            $slug = md5($this->id);
        }

        return $slug;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Forum
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return Forum
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return Forum
     */
    public function setDateAjout($dateAjout)
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return \DateTime 
     */
    public function getDateAjout()
    {
        return $this->dateAjout;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return Forum
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get old_id
     *
     * @return integer 
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * Gets the number of threads
     *
     * @return integer
     */
    public function getNumThreads()
    {
        return $this->numThreads;
    }

    /**
     * Sets the number of threads
     *
     * @param integer $threads
     */
    public function setNumThreads($threads)
    {
        $this->numThreads = intval($threads);
    }

    /**
     * Increments the number of threads by the supplied
     * value.
     *
     * @param  integer $by Value to increment threads by
     * @return integer The new thread total
     */
    public function incrementNumThreads($by = 1)
    {
        return $this->numThreads += intval($by);
    }

    /**
     * Set clan
     *
     * @param \App\Entity\Clan\Clan $clan
     * @return Forum
     */
    public function setClan(\App\Entity\Clan\Clan $clan = null)
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return \App\Entity\Clan\Clan 
     */
    public function getClan()
    {
        return $this->clan;
    }

    /**
     * Set canUserCreateThread
     *
     * @param boolean $canUserCreateThread
     * @return Thread
     */
    public function setCanUserCreateThread($canUserCreateThread)
    {
        $this->canUserCreateThread = $canUserCreateThread;

        return $this;
    }

    /**
     * Get canUserCreateThread
     *
     * @return boolean 
     */
    public function getCanUserCreateThread()
    {
        return $this->canUserCreateThread;
    }
}
