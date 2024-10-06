<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

class RegisterBlockTypes
{
    private BlockType|array $blockTypes;

    private AcfLoader $acfLoader;


    public function __construct(BlockType|array $blockTypes) {
        $this->blockTypes = $blockTypes;
        $this->acfLoader = new AcfLoader();
    }

    public function register() {
        
        if(!is_array($this->blockTypes)) {
            $this->blockTypes = [$this->blockTypes];
        }

        foreach($this->blockTypes as $blockTypeClass) {
            if(!is_subclass_of($blockTypeClass, BlockType::class)) {
                throw new \Exception('$blockType must by of type ' . BlockType::class);
            }
            
            add_action('acf/init', function() use ($blockTypeClass) {
                $this->registerBlockType($blockTypeClass);
            });
            $this->acfLoader->registerComponent($blockTypeClass);
        }

        $this->acfLoader->registerSyncJson();
    }

    public function registerBlockType($blockType)
    {
        register_block_type($blockType::getLocatedBlockJsonPath());
    }
}
