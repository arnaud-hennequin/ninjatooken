<?php

namespace App\Utils;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GameData
{
    private \DOMDocument $document;
    private string|false $xml;

    private int $experienceRelatif;
    private ?\DOMElement $levelActuel;
    private ?\DOMElement $levelSuivant;

    /**
     * @var \DOMNodeList<\DOMElement>
     */
    private \DOMNodeList $domExperience;

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
        /** @var ?\DOMElement $nodeElement */
        $nodeElement = $this->domExperience->item($this->domExperience->length - 2);
        if ($nodeElement !== null) {
            $this->experienceRelatif = $experience - $dan * (int) $nodeElement->getAttribute('val');
        }
        /** @var \DOMElement $exp */
        foreach ($this->domExperience as $exp) {
            if ($exp->getAttribute('val') <= $this->experienceRelatif) {
                ++$k;
            } else {
                break;
            }
        }
        /** @var ?\DOMElement $domElement */
        $domElement = $this->domExperience->item($k > 0 ? $k - 1 : 0);
        $this->levelActuel = $domElement;
        /** @var ?\DOMElement $domElement */
        $domElement = $this->domExperience->item($k);
        $this->levelSuivant = $domElement;

        return $this;
    }

    public function getLevelActuel(): int
    {
        return (int) $this->levelActuel->getAttribute('niveau');
    }

    public function getRatio(): float|int
    {
        return ($this->experienceRelatif - (int) $this->levelActuel?->getAttribute('val')) / ((int) $this->levelSuivant?->getAttribute('val') - (int) $this->levelActuel?->getAttribute('val')) * 100;
    }
}
