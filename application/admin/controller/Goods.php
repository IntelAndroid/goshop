<?php

namespace app\admin\controller;

use app\admin\model\Classify;
use app\admin\model\Good;
use app\admin\model\Price;
use app\common\Cate;
use app\common\UploadHandle;
use think\Cache;
use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;


class Goods extends Controller
{
    //【显示页】打开分分类管理
    public function cate_manage()
    {
        isNotLogin();
        $conn = Cate::install()->getConnect();
        $res = Cate::install()->getAllCate($conn, 'ty_classify', "1");
        $this->assign('data', $res);
        return $this->fetch('/goods/product-category');
    }

    //【显示页】展示商品
    public function add_good_list()
    {
        isNotLogin();
        $data = array();
        try {
            $arrs = Price::all(function ($query) {
                $query->order('id', 'desc');
            });
            foreach ($arrs as $k) {
                array_push($data, $k->toArray());
            }
        } catch (Exception $e) {
            exit;
        };
        $conn = Cate::install()->getConnect();
        $res = Cate::install()->getAllCate($conn, 'ty_classify', "1");
        $this->assign('data', $res);
        $this->assign('showNumber', count($data));
        $this->assign('showData', $data);
        return $this->fetch('/goods/product-list');
    }

    //【显示页】添加商品
    public function add_product()
    {
        isNotLogin();
        UploadHandle::install()->delImgName('goods_img_1');
        $conn = Cate::install()->getConnect();
        $res = Cate::install()->getAllCate($conn, 'ty_classify', "1");
        $this->assign('data', $res);
        $this->assign('upload_url', '/admin/upload/g_g_img');
        $this->assign('save_url', '/admin/goods/s_g_val');
        return $this->fetch('/goods/product-add');
    }

    //【显示页】修改商品
    public function edit_product()
    {
        isNotLogin();
        return $this->fetch('/goods/test');
//        return $this->fetch('/goods/product_edit');
    }

    //【显示数据分类】展示树杈在分类管理数据
    public function show_data_free()
    {
        $conn = Cate::install()->getConnect();
        $res = Cate::install()->getFileCate($conn, 'ty_classify', "1");
        echo json_encode($res);
    }

    //【存储数据分类】获取提交分类数据保存数据库
    public function cate_data_save()
    {
        isNotLogin();
        if(empty($_POST)){
            echo messageJson(400, 'error', null);
            exit;
        }
        $this->clearCache();
        $data['name'] = submit_input(input('cate_name'));
        $data['pid'] = submit_input(input('pid'));
        $cate = Classify::install();
        if ($_POST['cate_name'] != '' && $_POST['pid'] != 0) {
            try {
                $spilt = explode(',', $data['pid']);
                $path = $cate->field('path')->where(['id' => $spilt[0], 'pid' => $spilt[1]])->find();
                $data['path'] = $path['path'];
                $data['level'] = substr_count($data['path'], ',');
                $cate->save($data);
                $p['id'] = $cate->id;
                $p['path'] = $data['path'] . ',' . $cate->id;
                $p['level'] = substr_count($p['path'], ',');
                if ($cate->save($p)) {
                    echo messageJson(200, '添加成功', null);
                } else {
                    echo messageJson(400, '添加失败', null);
                    exit;
                }
            } catch (DbException $e) {
                echo messageJson(400, 'error', null);
                exit;
            }
        } else if ($_POST['cate_name'] != '' && $_POST['pid'] == 0) {
            try {
                $data['path'] = $_POST['pid'];
                $data['level'] = 1;
                $cate->save($data);
                $paths['id'] = $cate->id;
                $paths['path'] = $data['path'] . ',' . $cate->id;
                if ($cate->save($paths)) {
                    echo messageJson(200, '添加成功', null);
                } else {
                    echo messageJson(400, '添加失败', null);
                }
            } catch (DbException $e) {
                echo messageJson(400, 'error', null);
                exit;
            }
        } else {
            echo messageJson(400, '添加失败,内容不为空', null);
            exit;
        }

    }

    //【存储数据商品】保存商品传入的数据
    public function s_g_val()
    {
        isNotLogin();
        $jumpUrl = '/admin/goods/add_product';
        $imgUrl = UploadHandle::install()->getImgName('goods_img_1');
        if (empty($imgUrl)) {
            alertMes('上传图片不为空', $jumpUrl);
            exit;
        }
        if (empty($_POST['editor_content'])) {
            alertMes('编辑内容不为空', $jumpUrl);
            exit;
        }
        $this->clearCache();
        $g = Price::install();
        $vals = explode(',', submit_input(input('cate_select')));
        $g->data([
            'name' => submit_input(input('product_title')),
            'sketch' => submit_input(input('product_sketch')),
            'link' => submit_input(input('product_link')),
            'tid' => $vals[0],
            'tpid' => (empty($vals[1])) ? 0 : $vals[1],
            'unit' => submit_input(input('unit_select')),
            'attributes' => submit_input(input('attributes')),
            'path' => serialize($imgUrl),
            'curprice' => submit_input(input('display_price')),
            'nowprice' => submit_input(input('market_price')),
            'inventory' => submit_input(input('inventory')),
            'restrict' => submit_input(input('limit_buy')),
            'already' => submit_input(input('already_buy')),
            'freight' => submit_input(input('freight')),
            'status' => submit_input(input('up_rack')),
            'reorder' => is_numeric(input('sort_value')) ? input('sort_value') : 0,
            'details' => removeXSS(input('editor_content'))
        ]);
        $g->allowField(true)->save();
        UploadHandle::install()->delImgName('goods_img_1');
        if (empty($g->id)) {
            echo messageJson(400, '保存失败', null);
        } else {
            echo messageJson(200, '保存成功', null);
        }
        exit;
    }

    //【删除数据商品】删除商品
    public function del_product()
    {
        isNotLogin();
        if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
            $this->clearCache();
            try {
                $path = Price::install()->field(['path', 'details'])->where('id', 'eq', $_GET['id'])->find()->toArray();
                if (!empty($path['path'])) {
                    foreach ($path['path'] as $v) {
                        deleteOneFile(getImgUrl($v));
                    }
                }
                if (!empty($path['details'])) {
                    $alls = getAllImgSrc($path['details']);
                    if (count($alls) != 0) {
                        foreach ($alls as $k => $v) {
                            deleteOneFile(getImgUrl($v['path']));
                        }
                    }
                }
                Price::install()->where('id', '=', $_GET['id'])->delete();
                echo messageJson(200, '删除成功', null);
            } catch (Exception $e) {
                echo messageJson(400, 'error', null);
                exit;
            };
        } else {
            echo messageJson(400, '条件不符', null);
        }
        exit;
    }

    //【编辑数据分类】编辑分类管理的分类数据
    public function cate_edit_data()
    {
        isNotLogin();
        if (empty($_POST)) {
            echo messageJson(400, '不为空', null);
            exit;
        }
        $this->clearCache();
        if (is_numeric(input('id'))) {
            Classify::install()->allowField(true)->save([
                'name' => filterSpecialChar(input('name'))
            ], ['id' => input('id')]);
            echo messageJson(200, '修改成功', null);
        } else {
            echo messageJson(400, 'error!', null);
            exit;
        }
    }

    //【删除数据分类】删除分类管理的分类数据
    public function click_data_del()
    {
        isNotLogin();
        $id = submit_input(input('id'));
        $this->clearCache();
        try {
            $data = Classify::install()->where("pid=" . $id)->find();
            if ($data) {
                echo messageJson(200, '分类下面还子分类,不允许删除', null);
                exit;
            } else {
                Classify::install()->where("id=" . $id)->delete();
                echo messageJson(200, '删除成功', null);
                exit;
            }
        } catch (DbException $e) {
            $this->error('内容为空', '/admin');
            exit;
        }
    }

//【批量删除数据分类】
    public function cate_all_del()
    {
        isNotLogin();
        if (empty($_GET)) {
            echo messageJson(400, '不为空', null);
            exit;
        }
        $this->clearCache();
        $ids = trim(input('ids'), ',');
        Classify::destroy(submit_input($ids));
        echo messageJson(200, '删除成功', null);
        exit;
    }

//【商品详情】商品详情展示
    public function p_show()
    {
        isNotLogin();
        $name_arr = array();
        if (empty($_GET['id'])) {
            echo "<div style='width: 100%;text-align: center;color: #ff2812'><p>查询不为空!</p></div>";
            exit;
        }
        if (is_numeric(input('id'))) {
            try {
                $a = Price::get($_GET['id'])->toArray();
                if ($a['tid'] != 0) {
                    $con = Cate::install()->getConnect();
                    $pa = Cate::install()->getPathCate($con, 'ty_classify', $a['tid']);
                    foreach ($pa as $name) {
                        array_push($name_arr, $name['name'] . '->');
                    }
                    $this->assign('cate', trim(implode($name_arr), '->'));
                } else {
                    $this->assign('cate', '顶级分类');
                }
                $this->assign('data', $a);
            } catch (Exception $e) {
                echo "<div style='width: 100%;text-align: center;color: #ff2812'><p>查询出错!</p></div>";
                exit;
            }

        }
        return $this->fetch('/goods/picture-show');
    }

//【商品搜索】商品搜索
    public function s_s()
    {
        isNotLogin();
        $datas = array();
        $pid = input('pid');
        if (empty($_GET['pro'])) {
            if ($pid == 0) {
                echo messageJson(500, '当前已是全部', []);
                exit;
            }
            $arr_id = explode(',', $pid);
            try {
                if ($arr_id[1] == 0) {
                    $where['tpid'] = filterSpecialChar($arr_id[0]);
                    $s = Price::install()->where($where)->order('id desc')->select();
                } else {
                    $where['tid'] = filterSpecialChar($arr_id[0]);
                    $where['tpid'] = filterSpecialChar($arr_id[1]);
                    $s = Price::install()->where($where)->order('id desc')->select();
                }
                foreach ($s as $v) {
                    array_push($datas, $v->toArray());
                }
            } catch (Exception $e) {
                exit;
            }

        } else {
            try {
                $where['name'] = array('like', '%' . filterSpecialChar(input('pro')) . '%');
                $s = Price::install()->where($where)->order('id desc')->select();
                foreach ($s as $v) {
                    array_push($datas, $v->toArray());
                }
            } catch (Exception $e) {
                exit;
            };
        }
        echo messageJson(200, 'success', $datas);
    }

    public function all_del()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '没有任何选项', null);
            exit;
        }
        $this->clearCache();
        $ids = submit_input(input('id'));
        $arr_id = explode(',', $ids);
        try {
            if (!empty($arr_id)) {
                foreach ($arr_id as $id) {
                    $arr_d = Price::install()->where('id', 'eq', $id)->field(['path', 'details'])->find()->toArray();
                    if (!empty($arr_d['path'])) {
                        foreach ($arr_d['path'] as $va) {
                            deleteOneFile(getImgUrl($va));
                        }
                    }
                    if (!empty($arr_d['details'])) {
                        $alls = getAllImgSrc($arr_d['details']);
                        if (count($alls) != 0) {
                            foreach ($alls as $k => $v) {
                                deleteOneFile(getImgUrl($v['path']));
                            }
                        }
                    }
                }

            }
            Price::destroy($ids);
            echo messageJson(200, '全部清空', null);
        } catch (Exception $e) {
            exit;
        }
        exit;

    }

    //清理所有缓存
    private function clearCache()
    {
        Cache::set('home_program_cache', null);
        Cache::set('home_webs_cache', null);
        Cache::set('see_webs_cache', null);
        Cache::set('see_program_cache', null);
    }

}