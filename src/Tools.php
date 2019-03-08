<?php

namespace Esol\Sy\Tools;

class Tools
{

    /**
     * Retourne le r�pertoire o� se trouve composer.json 
     * en remontant dans l'arborescence � partir du r�pertoire courant ( __DIR__ )
     * quand aucun param�tre n'a �t� pass�.
     * Le param�tre $d est donc optionnel
     * 
     * @param string $d
     *
     * @return string
     */
    public function getRootDir()
    {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $d = null;
        if ($numargs == 0) {
            $d = __DIR__;
        }
        if ($numargs == 1) {
            $d = $arg_list[0];
        }
        return $this->getRootDir_1($d);
    }

    /**
     * Retourne le r�pertoire o� se trouve composer.json et qui n'appartient pas � un r�pertoire s'appelant vendor 
     * en remontant dans l'arborescence � partir du r�pertoire pass� en param�tre
     * Cette fonction n'est appel�e qu'� partir de getRootDir()
     * 
     * @param string $d
     *
     * @return string
     */
    private function getRootDir_1($d)
    {
        $vToReturn = null;
        $a = scanDir($d);
        if (in_array('composer.json', $a, true) && !strpos($d, 'vendor')) {
            $vToReturn = $d;
        } else {
            $d = $this->getParentDir($d);
            $vToReturn = $this->getRootDir($d);
        }
        return $vToReturn;
    }

    /**
     * return true s'il y a une r�pertoire app � la racine du projet
     * 
     * @return boolean
     */
    public function isAppTreeStructure()
    {
        $vToReturn = false;
        if (is_dir($this->getRootDir() . '/app')) {
            $vToReturn = true;
        }
        return $vToReturn;
    }

    /**
     * return le chemin du project_dir ( app ou root_dir )
     * 
     * @return string
     */
    public function getProjectDir()
    {
        $d = $this->getRootDir();
        if ($this->isAppTreeStructure()) {
            $d .= '/app';
        }
        return $d;
    }

    /**
     * Cr�er le chemin de r�pertoire � partir de la racine du projet s'il n'exite pas
     * 
     * @param string $d
     * 
     * @return void
     */
    public function buildRelativePathDir($d)
    {
        $d = $this->getProjectDir() . '/' . $d;
        if (!is_dir($d)) {
            mkdir($d, 0777, true);
        }
    }



    /**
     * return le r�pertoire parent du r�pertoire pass� en param�tre
     * 
     * @param string $d
     * 
     * @return string    
     */
    function getParentDir($d)
    {
        $lastSlashPos = null;
        if (strpos($d, '\\') > -1) {
            $lastSlashPos = strrpos($d, '\\');
        } else {
            $lastSlashPos = strrpos($d, '/');
        }
        $d = substr($d, 0, $lastSlashPos);
        return $d;
    }

    /**
     * TODO Supprime tous les dossiers vides d'une arborescence
     * 
     * @param string $d
     * 
     * @return void
     */
    public function rmEmptyDirectory($d)
    {

        $dir_iterator = new \RecursiveDirectoryIterator($d);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $f) {
            if ($f->isDir()) {
                try {
                    rmDir($f);
                } catch (Exception $e) {
                    echo 'Exception re�ue : ', $e->getMessage(), "\n";
                }
            }
        }
    }

    /**
     * place en variable d'environnement les parametres du fichier .env � la racine
     * @return void
     */
    public function LoadDotEnv()
    {
        $rootDotEnvPath = $this->getRootDir() . '/.env';
        try {
            if (!file_exists($rootDotEnvPath)) {
                file_put_contents($rootDotEnvPath, "");
            }    
            $lines = file($rootDotEnvPath);
            /*On parcourt le tableau $lines et on affiche le contenu de chaque ligne pr�c�d�e de son num�ro*/
            foreach ($lines as $lineNumber => $lineContent) {
                if (substr($lineContent, 0, 1) != "#" && strpos($lineContent, "=") > 0) {
                    putenv($lineContent);
                }
            }
        } catch (Exception $e) {
            echo 'Exception re�ue : ',  $e->getMessage(), "\n";
        }
    }
}
