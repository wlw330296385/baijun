<?php
namespace App\Api\Controllers;
use Phalcon\Logger\Adapter\File as FileAdapter;
use App\Api\Models\City;
use Phalcon\Crypt;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro;
class IndexController extends Controller
{

    public function indexAction()
    {
//  print_r($this->dispatcher->getControllerName());
        $city=new City();
        var_dump($city->find()->toArray());
        print_r(City::findFirst()->toArray());
    }

    /**
     * @param $name
     */
    public function testAction(){
        $name=(string) $_GET["table"];
//Executing a simple query
        $query  = $this->modelsManager->createQuery("SELECT * FROM :name:");
        $cars   = $query->execute();

//With bound parameters
        $query  = $this->modelsManager->createQuery("SELECT * FROM City WHERE name = :name:");
        $cars   = $query->execute(array(
            'name' => '南宁'));
        print_r($cars);
    }

    public function logAction(){
        if(!is_dir("./runtime")){
            mkdir("./runtime");
        }
        $logger = new FileAdapter("./runtime/log.log");  //初始化文件地址
        // 开启事务
        $logger->begin();
// 添加消息
        $logger->alert("This is an alert");
        $logger->error("This is another error");

//  保存消息到文件中
        $logger->commit();

    }
}

