<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

use ReflectionClass;
use Timber\Timber;
use WP_Block;

class BlockType
{
    final public function __construct() {}

    protected const ALLOWED_BLOCK = [];

    protected const TEMPLATE = [];

    protected const TEMPLATE_LOCK = false;

    protected const IS_WRAP_BLOCK = false;

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
    
    public function renderMod(array $block, string $content, bool $is_preview, int $post_id, ?WP_Block $acfContext): null|bool|string
    {
        $context = Timber::get_context();

        $context['slug'] = static::getName();


        $context['is_preview'] = $is_preview;
        $context['post_id'] = $post_id;
        $context['content'] = $content;

        // Store block values.
        $context['block'] = $block;

        // The context provided to the block by the post or its parent block
        $context['context'] = $acfContext;

        // Store field values.
        $context['fields'] = get_fields($block['id']);

        // Set parent context
        if(isset($block['parent']) && !empty($block['parent']) && count($block['parent'])) {
            foreach($block['parent'] as $parent) {
                if($context['context'] && $context['context']->context && key_exists($parent, $context['context']->context)) {
                    $context['parent_fields'] = $context['context']->context[$parent];
                }
            }
        }

        // Allowed blocks
        $context['allowed_blocks'] = count(static::ALLOWED_BLOCK) ? $this->jsonEncode(static::ALLOWED_BLOCK) : false;
        $context['templateLock'] = static::TEMPLATE_LOCK;

        // Template
        $context['template'] = count(static::TEMPLATE) ? $this->jsonEncode(static::TEMPLATE) : false;

        $context = $this->hook($context, $block, $content, $is_preview, $post_id, $acfContext);

        // TODO : Replace array data in context by BlockData

        if (file_exists(get_theme_file_path("/views/blocks/{$context['slug']}.twig"))) {
            return Timber::render("blocks/{$context['slug']}.twig", $context);
        }

        return null;
    }

    public static function renderCallback(array $block, string $content, bool $is_preview, int $post_id, ?WP_Block $context): bool|string|null
    {
        return (new static())->renderMod($block, $content, $is_preview, $post_id, $context);
    }

    public function isWrapperBlock(): bool
    {
        return static::IS_WRAP_BLOCK;
    }

    protected function jsonEncode(array $blocks): ?string
    {
        return esc_attr(wp_json_encode($blocks));
    }

    protected function hook(array $context, array $block, string $content, bool $is_preview, int $post_id, ?WP_Block $acfContext): array
    {
        return $context;
    }
}
