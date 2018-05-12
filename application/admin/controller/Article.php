<?php

namespace app\admin\controller;

use app\admin\model\Articles;
use app\common\Pages;
use app\common\SaveData;
use app\common\UploadHandle;
use think\Cache;
use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class Article extends Controller
{
    //文章添加
    public function a()
    {
        isNotLogin();
        UploadHandle::install()->delImgName('article_img_2');
        $this->assign('upload_url', '/admin/upload/art_img');
        $this->assign('save_url', '/admin/article/s');
        return $this->fetch('/article/add_article');

    }

    //查看文章
    public function q()
    {
        isNotLogin();
        $data = SaveData::install()->setSingleCache(function () {
            $data = array();
            try {
                $art = Articles::all(function ($query) {
                    $query->order('id desc');
                });
                foreach ($art as $val) {
                    array_push($data, $val);
                }
            } catch (DbException $e) {
                exit;
            }
            return $data;
        }, 'admin_article_caches');
        $pager = new Pages($data);
        $this->assign('data', $pager);
        return $this->fetch('/article/query_article');

    }

    //保存数据
    public function s()
    {
        isNotLogin();
        $jumpUrl = '/admin/article/a';
        $imgUrl = UploadHandle::install()->getImgName('article_img_2');
        if (empty($imgUrl)) {
            alertMes('上传图片不为空', $jumpUrl);
            exit;
        }
        if (empty($_POST['editor_content'])) {
            alertMes('编辑内容不为空', $jumpUrl);
            exit;
        }
        Cache::set('admin_article_caches',null);
        Cache::set('home_article_cache',null);
        Cache::set('home_article_cache2',null);
        $art = Articles::install();
        $art->data([
            'title' => submit_input(input("article_title")),
            'article_type' => submit_input(input("article_type")),
            'keywords' => mb_substr(submit_input(input("article_title")), 0, 5, 'utf-8'),
            'email' => submit_input(input("author_email")),
            'link' => submit_input(input("article_link")),
            'publish_time' => submit_input(input("article_time")),
            'abstract' => submit_input(input("article_abstract")),
            'thumb' => $imgUrl,
            'content' => removeXSS(input("editor_content"))
        ], true);
        $art->allowField(true)->save();
        if (empty($art->id)) {
            $this->error('保存失败', '/admin/article/a');
        } else {
            $this->success('保存成功', '/admin/article/a');
        }
        UploadHandle::install()->delImgName('article_img_2');

    }

//显示搜索内容
    public function sx()
    {
        isNotLogin();
        if (empty($_POST['data'])) {
            echo messageJson(400, 'err', null);
            exit;
        }
        $keywords = json_decode(input('data'))->key;
        if (empty($keywords)) {
            echo messageJson(400, 'err', null);
            exit;
        }
        try {
            $data = array();
            $where['keywords'] = array('like', '%' . filterSpecialChar($keywords) . '%');
            $a = Articles::install()->where($where)->order('id desc')->select();
            foreach ($a as $va) {
                array_push($data, $va->toArray());
            }
            if (empty($data)) {
                echo messageJson(400, '没有搜索到{' . filterSpecialChar($keywords) . '}关键词!', null);

            } else {
                echo messageJson(200, 'success', $data);
            }
            unset($data);
        } catch (DbException $e) {
            exit;
        }
    }

    //查看文章
    public function see()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            $this->error('查看出错', '/admin/article/q');
            exit;
        }
        if (is_numeric(input('id'))) {
            try {
                $p = Articles::get(function ($q) {
                    $q->where('id', 'eq', input('id'))->field(['content']);
                })->toArray();
            } catch (Exception $e) {
                exit;
            }
            $this->assign('content', $p['content']);
            return $this->fetch('/article/content');
        } else {
            $this->error('查看出错', '/admin/article/q');
        }

    }

//删除文章
    public function del()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            $this->error('删除失败', '/admin/article/q');
            exit;
        }
        if (is_numeric(input('id'))) {
            Cache::set('admin_article_caches',null);
            Cache::set('home_article_cache',null);
            Cache::set('home_article_cache2',null);
            try {
                $p = Articles::get(function ($q) {
                    $q->where('id', 'eq', input('id'))->field(['content', 'thumb']);
                })->toArray();
                if (!empty($p['thumb'])) {
                    foreach ($p['thumb'] as $url) {
                        deleteOneFile(getImgUrl($url));
                    }
                }
                if (!empty(getAllImgSrc($p['content']))) {
                    foreach (getAllImgSrc($p['content']) as $val) {
                        deleteOneFile(getImgUrl($val['path']));
                    }
                }
                Articles::destroy(input('id'));
                $this->success('删除成功', '/admin/article/q');
                exit;
            } catch (Exception $e) {
                exit;
            }
        }

    }


}