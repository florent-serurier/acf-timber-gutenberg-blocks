<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

use Laminas\Filter\Word\CamelCaseToDash;
use ReflectionClass;
use Timber\Timber;
use WP_Block;

class BlockType
{
    private AcfLoader $acfLoader;

    final public function __construct() {
        $this->acfLoader = new AcfLoader();
    }

    public static function getName(): ?string
    {
        $filter = new CamelCaseToDash();
        
        $reflectParent = new ReflectionClass(self::class);
        $reflectBlock = new ReflectionClass(static::class);

        return strtolower($filter->filter(str_replace($reflectParent->getShortName(), '', $reflectBlock->getShortName())));
    }

    public static function getBlockName(): string
    {
        return 'acf/' . static::getName();
    }

    /**
     * Return the path to the folder where the block.json file is located
     */
    public static function getLocatedBlockJsonPath(): string
    {
        $class_info = new ReflectionClass(get_called_class());
        $blockJsonPath = dirname($class_info->getFileName());

        if(!file_exists($blockJsonPath . DIRECTORY_SEPARATOR . 'block.json')) {
            throw new \Exception("block.json file doesn't exits in {$blockJsonPath}");
        }

        return $blockJsonPath;
    }

    public static function getAcfJsonPath(): string
    {
        return static::getLocatedBlockJsonPath() . DIRECTORY_SEPARATOR . 'Acf';
    }
    
    public function renderMod(array $block, string $content, bool $is_preview, int $post_id, WP_Block $wpBlock, array $blockContext): null|bool|string
    {
        $context = Timber::get_context();

        $context['slug'] = static::getName();

        $context['data'] = $this->getData($block, $content, $is_preview, $post_id, $wpBlock, $blockContext);

        if (file_exists(get_theme_file_path("/views/blocks/{$context['slug']}.twig"))) {
            return Timber::render("blocks/{$context['slug']}.twig", $context);
        }

        return null;
    }

    private function getData(array $block, string $content, bool $is_preview, int $post_id, WP_Block $wpBlock, array $blockContext): BlockData
    {
        $reflectParent = new ReflectionClass(self::class);
        $reflectBlock = new ReflectionClass(static::class);
        $className = $reflectBlock->getNamespaceName() . '\\' . str_replace($reflectParent->getShortName(), '', $reflectBlock->getShortName()) . 'BlockData';
        $className = class_exists($className) ? $className : BlockData::class;
        
        return new $className($block, $content, $is_preview, $post_id, $wpBlock, $blockContext);
    }

    public static function renderCallback(array $block, string $content, bool $is_preview, int $post_id, WP_Block $wpBlock, array $context): bool|string|null
    {
        return (new static())->renderMod($block, $content, $is_preview, $post_id, $wpBlock, $context);
    }
}
