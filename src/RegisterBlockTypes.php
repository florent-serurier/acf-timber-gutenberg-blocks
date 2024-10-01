<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

class RegisterBlockTypes
{
    public BlockType|array $blockTypes;

    public function __construct(BlockType|array $blockTypes) {
        $this->blockTypes = $blockTypes;
    }

    public function register() {
        add_action('acf/init', [$this, 'registerBlockTypes']);
    }

    public function registerBlockTypes()
    {
        if(!is_array($this->blockTypes)) {
            $this->blockTypes = [$this->blockTypes];
        }

        foreach($this->blockTypes as $blockTypeClass) {
            if(!is_subclass_of($blockTypeClass, BlockType::class)) {
                throw new \Exception('$blockType must by of type ' . BlockType::class );
            }
            
            register_block_type($blockTypeClass::getLocatedBlockJsonPath());
        }

    }
}
