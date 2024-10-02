<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

use FlorentSerurier\AcfTimberGutembergBlocks\Interface\BlockDataInterface;
use WP_Block;

class BlockData implements BlockDataInterface
{
    /**
     * 'horizontal'|'vertical'|undefined
     */
    private string $orientation = 'vertical';

    private ?array $defaultBlock;
    
    private bool $directInsert = false;

    private array $template = [];
    
    /**
     * 'contentOnly'|'all'|'insert'|false
     */
    private string|false $templateLock = false;

    private array|bool $allowedBlocks = true;

    private array|false $fields;

    /**
     * 
     */
    public function __construct(
        private array $block, 
        private string $content, 
        private bool $is_preview, 
        private int $post_id, 
        private WP_Block $wpBlock, 
        private array $blockContext
    ) {}

    public function getFields(): array|false
    {
        if(!isset($this->fields)) {
            $this->fields = get_fields($this->block['id']);
        }

        return $this->fields;
    }

    public function getParentFields(): ?array
    {
        if(isset($this->block['parent']) && !empty($this->block['parent']) && count($this->block['parent'])) {
            foreach($this->block['parent'] as $parent) {
                if(key_exists($parent, $this->blockContext)) {
                    return $this->blockContext[$parent];
                }
            }
        }

        return null;
    }

    public function isPreview(): bool
    {
        return $this->is_preview;
    }

    public function getAllowedBlocks(): string|false
    {
        if(!is_array($this->allowedBlocks)) {
            return $this->allowedBlocks;
        }

        return $this->jsonEncode($this->allowedBlocks);
    }

    public function getTemplate(): string|false
    {
        if(!is_array($this->template)) {
            return $this->template;
        }

        return $this->jsonEncode($this->template);
    }

    protected function jsonEncode(array $data): ?string
    {
        return esc_attr(wp_json_encode($data));
    }
}
