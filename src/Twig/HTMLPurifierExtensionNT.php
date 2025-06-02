<?php

namespace App\Twig;

use App\Utils\HTMLPurifier;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HTMLPurifierExtensionNT extends AbstractExtension
{
    public HTMLPurifier $htmlPurifier;
    /**
     * @var array<string, HTMLPurifier>
     */
    private array $purifiers = [];

    /**
     * Constructor.
     */
    public function __construct(HTMLPurifier $htmlPurifier)
    {
        $this->htmlPurifier = $htmlPurifier;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('purify', [$this, 'purify'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Filter the input through an HTMLPurifier service.
     *
     * @param string|null $string $string
     */
    public function purify(?string $string, string $profile = 'default'): string
    {
        if (null === $string) {
            return '';
        }

        $HTMLPurifier = $this->getHTMLPurifierForProfile($profile);

        // ajoute certaines définitions
        if ('full' == $profile) {
            if ($def = $HTMLPurifier->config->maybeGetRawHTMLDefinition()) {
                $def->addAttribute('embed', 'allowfullscreen', 'Enum#true,false');
                $def->addAttribute('object', 'classid', 'CDATA');
                $def->addElement(
                    'iframe',
                    'Inline',
                    'Flow',
                    'Common',
                    [
                        'src' => 'URI#embedded',
                        'width' => 'Length',
                        'height' => 'Length',
                        'name' => 'ID',
                        'scrolling' => 'Enum#yes,no,auto',
                        'frameborder' => 'Enum#0,1',
                        'longdesc' => 'URI',
                        'marginheight' => 'Pixels',
                        'marginwidth' => 'Pixels',
                    ]
                );
                $def->addElement(
                    'span',
                    'Inline',
                    'Flow',
                    'Common',
                    [
                        'align' => 'Enum#left,right,center,justify',
                    ]
                );
                $def->addElement(
                    'li',
                    'Inline',
                    'Flow',
                    'Common',
                    [
                        'align' => 'Enum#left,right,center,justify',
                    ]
                );
                $def->addElement(
                    'fieldset',
                    'Block',
                    'Flow',
                    'Common',
                    []
                );
                $def->addElement(
                    'legend',
                    'Block',
                    'Flow',
                    'Common',
                    []
                );
            }
        }

        return $HTMLPurifier->purify($string);
    }

    /**
     * Get the HTMLPurifier service corresponding to the given profile.
     *
     * @throws \RuntimeException
     */
    private function getHTMLPurifierForProfile(string $profile): \HTMLPurifier
    {
        if (!isset($this->purifiers[$profile])) {
            $this->purifiers[$profile] = $this->htmlPurifier->get($profile);
        }

        return $this->purifiers[$profile];
    }
}
