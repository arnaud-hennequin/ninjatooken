<?php

namespace App\Utils;

use HTMLPurifier as HTMLPurify;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class HTMLPurifier
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $htmlConfig;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->htmlConfig = $parameterBag->get('html_purifier');
    }

    /**
     * @param string                              $profile       The configuration name, also used as cache id
     * @param array<string, mixed>                $configArray   The config array to set up
     * @param \HTMLPurifier_Config|null           $defaultConfig The default config that every config must inherit or null if
     *                                                           default
     * @param array<array<string, mixed>>         $parents       An array of config arrays to inherit by preloading or null
     * @param array<string, array<string, mixed>> $attributes    A nullable array of rules as arrays by tag name holding two
     *                                                           string elements, the first for the attribute name and the second
     *                                                           for the rule (i.e: Text, ID, ...)
     *                                                           [ ['img' => ['src' => 'URI', 'data-type' => Text']] ]
     * @param array<string, array<int, mixed>>    $elements      An array of arrays by element to add or override, arrays must
     *                                                           hold a type ("Inline, "Block", ...), a content type ("Empty",
     *                                                           "Optional: #PCDATA", ...), an attributes set ("Core", "Common",
     *                                                           ...), a fourth optional may define attributes rules as array, and
     *                                                           a fifth to list forbidden attributes
     * @param array<int, string>                  $blankElements An array of tag names that should not have any attributes
     */
    public static function create(
        string $profile,
        array $configArray,
        ?\HTMLPurifier_Config $defaultConfig = null,
        array $parents = [],
        array $attributes = [],
        array $elements = [],
        array $blankElements = []
    ): \HTMLPurifier_Config {
        if ($defaultConfig) {
            $config = \HTMLPurifier_Config::inherit($defaultConfig);
        } else {
            $config = \HTMLPurifier_Config::createDefault();
        }

        foreach ($parents as $parent) {
            $config->loadArray($parent);
        }

        $config->loadArray($configArray);

        // Make the config unique
        $config->set('HTML.DefinitionID', $profile);
        $config->set('HTML.DefinitionRev', 1);

        $def = $config->maybeGetRawHTMLDefinition();

        // If the definition is not cached, build it
        if ($def && ($attributes || $elements || $blankElements)) {
            static::buildHTMLDefinition($def, $attributes, $elements, $blankElements);
        }

        return $config;
    }

    /**
     * Builds a config definition from the given parameters.
     *
     * This build should never happen on runtime, since purifiers cache should
     * be generated during warm up.
     *
     * @param array<string, array<string, mixed>> $attributes
     * @param array<string, array<int, mixed>>    $elements
     * @param array<int, string>                  $blankElements
     */
    public static function buildHTMLDefinition(
        \HTMLPurifier_HTMLDefinition $def,
        array $attributes,
        array $elements,
        array $blankElements,
    ): void {
        foreach ($attributes as $elementName => $rule) {
            foreach ($rule as $attributeName => $definition) {
                /* @see \HTMLPurifier_AttrTypes */
                $def->addAttribute($elementName, $attributeName, $definition);
            }
        }

        foreach ($elements as $elementName => $config) {
            /* @see \HTMLPurifier_HTMLModule::addElement() */
            $el = $def->addElement($elementName, $config[0], $config[1], $config[2], $config[3] ?? []);

            if (isset($config[4])) {
                $el->excludes = array_fill_keys($config[4], true);
            }
        }

        foreach ($blankElements as $blankElement) {
            /* @see \HTMLPurifier_HTMLModule::addBlankElement() */
            $def->addBlankElement($blankElement);
        }
    }

    public function has(string $profile): bool
    {
        return isset($this->htmlConfig[$profile]);
    }

    public function get(string $profile): HTMLPurify
    {
        return new HTMLPurify($this->htmlConfig[$profile]);
    }
}
