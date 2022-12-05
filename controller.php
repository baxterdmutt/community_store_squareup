<?php
namespace Concrete\Package\CommunityStoreSquareup;

use Concrete\Core\Support\Facade\Route;
use Concrete\Core\Package\Package;
use Whoops\Exception\ErrorException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_squareup';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.0.1';
    protected $packageDependencies = ['community_store'=>'2.0'];
    
    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStoreSquareup\Src\CommunityStore',
    ];

    
    public function getPackageDescription()
    {
        return t("Square Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("SquareUp Payment Method");
    }

    public function install()
    {
        $installed = $this->app->make('Concrete\Core\Package\PackageService')->getInstalledHandles();

        if (!(is_array($installed) && in_array('community_store', $installed))) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        } else {
            $pkg = parent::install();
            $pm = new PaymentMethod();
            $pm->add('community_store_squareup', 'Square Payment Gateway', $pkg);
        }
    }
  
    public function uninstall()
    {
        $pm = PaymentMethod::getByHandle('community_store_squareup');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }
    public function on_start()
    {
        require 'vendor/autoload.php';
    }
}
