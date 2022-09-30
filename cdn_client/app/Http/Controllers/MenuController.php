<?php

namespace App\Http\Controllers;

use App\Dto\InputMenuDto;
use App\Dto\OutputMenuListDto;
use App\Services\ResponseService;
use App\Services\MenuService;
use App\Services\UtilService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{

    private $menuService;
    private $utilService;
    private $responseService;

    public function __construct(
        MenuService $menuService,
        UtilService $utilService,
        ResponseService $responseService
    ) {
        $this->menuService = $menuService;
        $this->utilService = $utilService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //取得api data
        $data = $request->query();

        //頁數初始化
        $pageManagement = $this->utilService->initPage($data ?? null);

        //取得List, page
        $menuList = $this->menuService->getMenuList($pageManagement);
        $menuPage = $this->menuService->getMenuPage($pageManagement);

        $outputMenuListDto = new OutputMenuListDto($menuList, $menuPage);
        return $this->responseService->responseJson($outputMenuListDto);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //取得api data
        $data = $request->all();

        //驗證
        $this->utilService->ColumnValidator($data, [
            'name' => 'required|unique:menus|max:100',
            'key' => 'required|unique:menus|max:150',
            'url' => 'required|max:500',
            'feature' => ['required', 'max:10', Rule::in(['T', 'P', 'F'])],
            'status' => 'required|boolean',
            'parent' => 'integer|nullable',
            'weight' => 'integer|nullable',
            'remark' => 'string|max:5000|nullable'
        ]);

        $menuDto = new InputMenuDto(
            $data["name"],
            $data["key"],
            $data["url"],
            $data["feature"],
            $data["status"],
            $data["parent"] ?? 0,
            $data["weight"] ?? "",
            $data["remark"] ?? "",
        );

        $this->menuService->createMenu($menuDto);
        return $this->responseService->responseJson();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->menuService->getMenuById($id);
        return $this->responseService->responseJson($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //取得api data
        $data = $request->all();

        //驗證
        $this->utilService->ColumnValidator($data, [
            'name' => 'max:100|unique:menus,name,' . $id,
            'key' => 'max:150|unique:menus,key,' . $id,
            'url' => 'max:500',
            'feature' => ['max:10', Rule::in(['T', 'P', 'F'])],
            'status' => 'boolean',
            'parent' => 'integer|nullable',
            'weight' => 'integer|nullable',
            'remark' => 'string|max:5000|nullable'
        ]);

        $menuDto = new InputMenuDto(
            $data["name"],
            $data["key"],
            $data["url"],
            $data["feature"],
            $data["status"],
            $data["parent"] ?? 0,
            $data["weight"] ?? "",
            $data["remark"] ?? "",
        );

        $this->menuService->updateMenu($menuDto, $id);
        return $this->responseService->responseJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->menuService->deleteMenuById($id);
        return $this->responseService->responseJson();
    }
}
