<?php

namespace Xpressengine\Plugins\XeroCommerce\Controllers\Settings;

use XePresenter;
use XeFrontend;
use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Models\Carrier;
use Xpressengine\Plugins\XeroCommerce\Models\Shop;
use Xpressengine\Plugins\XeroCommerce\Plugin\ValidateManager;
use Xpressengine\Plugins\XeroCommerce\Services\ShopService;
use Xpressengine\Plugins\XeroCommerce\Services\ShopUserService;

class ShopController extends SettingBaseController
{
    /** @var ShopService $shopService */
    protected $shopService;

    /** @var ShopUserService $shopUserService */
    protected $shopUserService;

    /**
     * ShopController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->shopService = new ShopService();
        $this->shopUserService = new ShopUserService();
    }

    /**
     * @param Request $request request
     *
     * @return XePresenter
     */
    public function index(Request $request)
    {
        $shops = $this->shopService->getShops($request);

        return XePresenter::make('shop.index', compact('shops'));
    }

    /**
     * @return XePresenter
     */
    public function create()
    {
        $shopTypes = Shop::getShopTypes();

        XeFrontend::rule('shop', ValidateManager::getShopValidateRules());

        return XePresenter::make('shop.create', compact('shopTypes'));
    }

    /**
     * @param Request $request request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $newShop = $this->shopService->create($request);

        return redirect()->route('xero_commerce::setting.config.shop.show', ['shopId' => $newShop->id]);
    }

    /**
     * @param Request $request request
     * @param int     $shopId  shopId
     *
     * @return XePresenter
     */
    public function show(Request $request, $shopId)
    {
        $shop = $this->shopService->getShop($shopId);
        $carriers = Carrier::all();

        return XePresenter::make('shop.show', compact('shop', 'carriers'));
    }

    /**
     * @param Request $request request
     * @param int     $shopId  shopId
     *
     * @return mixed
     */
    public function edit(Request $request, $shopId)
    {
        $shop = $this->shopService->getShop($shopId);
        $shopTypes = Shop::getShopTypes();

        XeFrontend::rule('shop', ValidateManager::getShopValidateRules());

        return XePresenter::make('shop.edit', compact('shop', 'shopTypes'));
    }

    /**
     * @param Request $request request
     * @param int     $shopId  shopId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $shopId)
    {
        $this->shopService->update($request, $shopId);

        return redirect()->route('xero_commerce::setting.config.shop.index');
    }

    /**
     * @param Request $request request
     * @param int     $shopId  shopId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request, $shopId)
    {
        if ($this->shopService->remove($shopId) == true) {
            $redirect = redirect()->route('xero_commerce::setting.config.shop.index')
                ->with('alert', ['type' => 'success', 'message' => '입점몰이 삭제 되었습니다.']);
        } else {
            $redirect = redirect()->route('xero_commerce::setting.config.shop.index')
                ->with('alert', ['type' => 'danger', 'message' => '기본 입점몰은 삭제 할 수 없습니다.']);
        }

        return $redirect;
    }

    public function getCarriers(Shop $shop)
    {
        return $shop->carriers;
    }

    public function addCarrier(Request $request, Shop $shop)
    {
        $this->shopService->addCarrier($request, $shop);
    }

    public function removeCarrier(Request $request, Shop $shop)
    {
        $this->shopService->removeCarrier($request, $shop);
    }
}
