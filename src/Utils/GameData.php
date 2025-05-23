<?php

namespace App\Utils;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GameData
{
    private \DOMDocument $document;
    private string|false $xml;

    private int $experienceRelatif;
    private $levelActuel;
    private $levelSuivant;

    private $domExperience;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $file = $parameterBag->get('kernel.project_dir').'/public/unity/game.xml';
        $this->xml = file_get_contents($file);
        $this->document = new \DOMDocument();
        $this->document->loadXml('<root>'.$this->xml.'</root>');

        $this->domExperience = $this->document->getElementsByTagName('experience')->item(0)->getElementsByTagName('x');
    }

    public function getDocument(): \DOMDocument
    {
        return $this->document;
    }

    public function getRaw(): bool|string
    {
        return $this->xml;
    }

    public function setExperience(int $experience = 0, int $dan = 0): GameData
    {
        $k = 0;
        $this->experienceRelatif = $experience - $dan * $this->domExperience->item($this->domExperience->length - 2)->getAttribute('val');
        foreach ($this->domExperience as $exp) {
            if ($exp->getAttribute('val') <= $this->experienceRelatif) {
                ++$k;
            } else {
                break;
            }
        }
        $this->levelActuel = $this->domExperience->item($k > 0 ? $k - 1 : 0);
        $this->levelSuivant = $this->domExperience->item($k);

        return $this;
    }

    public function getLevelActuel()
    {
        return $this->levelActuel->getAttribute('niveau');
    }

    public function getRatio(): float|int
    {
        return ($this->experienceRelatif - $this->levelActuel->getAttribute('val')) / ($this->levelSuivant->getAttribute('val') - $this->levelActuel->getAttribute('val')) * 100;
    }
}
