<?php

namespace App\Twig;

use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;

class HTMLPurifierExtensionNT extends HTMLPurifierExtension
{

    public $htmlPurifier;

    /**
     * Constructor.
     *
     * @param HTMLPurifiersRegistryInterface $htmlPurifier
     */
    public function __construct(HTMLPurifiersRegistryInterface $htmlPurifier)
    {
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * Filter the input through an HTMLPurifier service.
     *
     * @param string $string
     * @param string $profile
     * @return string
     */
    public function purify(string $string, string $profile = 'default'): string
    {
        $HTMLPurifier = $this->getHTMLPurifierForProfile($profile);

        // ajoute certaines définitions
        if($profile=='full'){
            if($def = $HTMLPurifier->config->maybeGetRawHTMLDefinition()) {
                $def->addAttribute('embed', 'allowfullscreen', 'Enum#true,false');
                $def->addAttribute('object', 'classid', 'CDATA');
                $def->addElement(
                    'iframe', 'Inline', 'Flow', 'Common',
                    array(
                        'src' => 'URI#embedded',
                        'width' => 'Length',
                        'height' => 'Length',
                        'name' => 'ID',
                        'scrolling' => 'Enum#yes,no,auto',
                        'frameborder' => 'Enum#0,1',
                        'longdesc' => 'URI',
                        'marginheight' => 'Pixels',
                        'marginwidth' => 'Pixels',
                    )
                );
                $def->addElement(
                    'span', 'Inline', 'Flow', 'Common',
                    array(
                        'align' => 'Enum#left,right,center,justify',
                    )
                );
                $def->addElement(
                    'li', 'Inline', 'Flow', 'Common',
                    array(
                        'align' => 'Enum#left,right,center,justify',
                    )
                );
                $def->addElement(
                    'fieldset',
                    'Block',
                    'Flow',
                    'Common',
                    array()
                );
                $def->addElement(
                    'legend',
                    'Block',
                    'Flow',
                    'Common',
                    array()
                );
            }
        }

        return $HTMLPurifier->purify($string);
    }

    /**
     * Get the HTMLPurifier service corresponding to the given profile.
     *
     * @param string $profile
     * @return \HTMLPurifier
     * @throws \RuntimeException
     */
    private function getHTMLPurifierForProfile(string $profile): \HTMLPurifier
    {
        if (!isset($this->purifiers[$profile])) {
            $purifier = $this->htmlPurifier->get($profile);

            if (!$purifier instanceof \HTMLPurifier) {
                throw new \RuntimeException(sprintf('Service "exercise_html_purifier.%s" is not an HTMLPurifier instance.', $profile));
            }

            $this->purifiers[$profile] = $purifier;
        }

        return $this->purifiers[$profile];
    }
}
