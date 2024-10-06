<?php

namespace FlorentSerurier\AcfTimberGutembergBlocks;

class AcfLoader
{
    private array $blocTypes;

    public function registerSyncJson()
    {
        $this->registerJsonLoadPoint();
        $this->registerJsonSavePoint();
    }

    private function registerJsonLoadPoint() 
    {
        add_filter('acf/settings/load_json', [$this, 'registerPathFields'], 20);
    }

    private function registerJsonSavePoint() 
    {
        add_filter("acf/json/save_paths", [$this, 'registerJsonSavePaths'], 20, 2);
    }

    public function registerJsonSavePaths($paths, $post): array
    {
        $location = $post['location'] ?? [];
        
        if(isset($location[0][0]) && is_array($location[0][0]) && count($location[0][0])) {
            $blockName = $location[0][0]['value'];
            
            foreach($this->blocTypes as $blockType) {
                if($blockType::getBlockName() == $blockName) {
                    $jsonPath = $blockType::getAcfJsonPath();
                    
                    if(!array_search($jsonPath, $paths)) {
                        $paths[] = $jsonPath;
                    }
                }
            }
        }
        return $paths;
    }

    public function registerComponent(string $blocType)
    {
        $this->blocTypes[] = $blocType;
    }

    public function registerPathFields(array $paths): array
    {
        return [...$paths, ...$this->jsonFieldsPath()];
    }

    private function jsonFieldsPath(): array
    {
        return array_map(function($blockType) {
            return $blockType::getAcfJsonPath();
        }, $this->blocTypes);
    }
}