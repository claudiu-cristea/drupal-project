<?php

declare(strict_types=1);

namespace Drupal\Project;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    private const array EXTENSION_TYPE = [
      'module' => 'modules',
      'profile' => 'profiles',
      'theme' => 'themes',
    ];

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        return [
          ScriptEvents::POST_INSTALL_CMD => 'installProject',
          ScriptEvents::POST_UPDATE_CMD => 'installProject',
        ];
    }

    public function installProject(Event $event): void
    {
        $this->setupExtensions();
        $this->setupSettings();
        $this->setupFiles();
        $this->ignoreWebroot();
    }

    private function setupExtensions(): void
    {
        foreach (self::EXTENSION_TYPE as $singular => $plural) {
            $sourceDir = 'src/Drupal/' . ucfirst($plural);
            $linkDir = "web/$plural/custom";

            if (!is_dir($sourceDir)) {
                mkdir(directory: $sourceDir, recursive: true);
                file_put_contents(
                    "$sourceDir/README",
                    "Remove this file after adding the first $singular."
                );
                $this->io->write("Created $sourceDir directory for custom $plural");
            }
            if (!is_link($linkDir)) {
                symlink("../../$sourceDir", $linkDir);
                $this->io->write(sprintf('Symlinked %s as %s', $sourceDir, $linkDir));
            }
        }
    }

    private function setupSettings(): void
    {
        $settingsDir = 'src/Drupal/Settings';
        if (!is_dir($settingsDir)) {
            mkdir(directory: $settingsDir, recursive: true);
            $this->io->write(sprintf('Created %s directory', $settingsDir));
        }

        $gitIgnoreSettingsLocal = "$settingsDir/.gitignore";
        if (!is_file($gitIgnoreSettingsLocal)) {
            file_put_contents($gitIgnoreSettingsLocal, "/settings.local.php\n");
        }

        $settingsFile = "$settingsDir/settings.php";
        if (!is_file($settingsFile)) {
            $package = $this->composer->getRepositoryManager()
              ->getLocalRepository()
              ->findPackage('claudiu-cristea/drupal-project', '*');
            $dir = $this->composer->getInstallationManager()
              ->getInstallPath($package);
            copy("$dir/code/settings.php.dist", "$settingsDir/settings.php");

            $this->io->write(sprintf('Created %s', $settingsFile));
        }

        $settingsLink = 'web/sites/default/settings.php';
        if (!is_link($settingsLink)) {
            if (file_exists($settingsLink)) {
                throw new \RuntimeException(sprintf(
                    'Cannot symlink %s as %s because a file already exists. ' .
                    'Remove the file and run this command again.',
                    $settingsFile,
                    $settingsLink,
                ));
            }

            symlink("../../../$settingsFile", $settingsLink);
            $this->io->write(sprintf('Symlinked %s as %s', $settingsFile, $settingsLink));
        }

        $settingsLocalFile = "$settingsDir/settings.local.php";
        if (!is_file($settingsLocalFile)) {
            file_put_contents($settingsLocalFile, "<?php\n");
            $this->io->write(sprintf(
                'Created %s file for custom local settings. This file is not under Git control',
                $settingsLocalFile
            ));
        }

        $settingsLocalLink = 'web/sites/default/settings.local.php';
        if (!is_link($settingsLocalLink)) {
            if (file_exists($settingsLocalLink)) {
                throw new \RuntimeException(sprintf(
                    'Cannot symlink %s as %s because a file already exists. ' .
                    'Remove the file and run this command again.',
                    $settingsLocalFile,
                    $settingsLocalLink,
                ));
            }

            symlink("../../../$settingsLocalFile", $settingsLocalLink);
            $this->io->write(sprintf('Symlinked %s as %s', $settingsLocalFile, $settingsLocalLink));
        }
    }

    private function setupFiles(): void
    {
        $publicFiles = 'files/public';
        if (!is_dir($publicFiles)) {
            mkdir(directory: $publicFiles, recursive: true);
            $this->io->write(sprintf('Created %s directory', $publicFiles));
        }

        $privateFiles = 'files/private';
        if (!is_dir($privateFiles)) {
            mkdir(directory: $privateFiles, recursive: true);
            $this->io->write(sprintf('Created %s directory', $privateFiles));
        }

        $gitIgnoreFiles = 'files/.gitignore';
        if (!is_file($gitIgnoreFiles)) {
            file_put_contents($gitIgnoreFiles, "/private/\n/public/\n");
        }

        $publicFilesLink = 'web/sites/default/files';
        if (!is_link($publicFilesLink)) {
            if (is_dir($publicFilesLink)) {
                throw new \RuntimeException(sprintf(
                    'Cannot symlink %s as %s because a directory already exists. ' .
                    'Remove the directory and run this command again.',
                    $publicFiles,
                    $publicFilesLink,
                ));
            }

            symlink("../../../$publicFiles", $publicFilesLink);
            $this->io->write(sprintf('Symlinked %s as %s', $publicFiles, $publicFilesLink));
        }
    }

    private function ignoreWebroot(): void
    {
        $contents = '';
        if (file_exists('.gitignore')) {
            $contents = trim(file_get_contents('.gitignore')) . "\n";

            if ($this->isWebrootIgnored($contents)) {
                return;
            }
        }
        $contents .= "/web/\n";
        file_put_contents('.gitignore', $contents);

        $this->io->write('Added /web/ to .gitignore');
    }

    private function isWebrootIgnored(string $contents): bool
    {
        $contents = str_replace("\r\n", "\n", $contents);
        return (bool) preg_match('~^/?web/?$~m', $contents);
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
