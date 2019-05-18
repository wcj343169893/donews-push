<?php
namespace Mofing\DoNewsPush\Contracts;

interface PushInterface
{

    /**
     * 发送消息
     *
     * @param string $deviceToken
     *            用户设备token
     * @param string $title
     *            通知栏展示的通知的标题。
     * @param string $message
     *            通知栏展示的通知的描述。
     * @param string $type
     *            message/quiet
     * @param [] $after_open
     *            点击后的效果
     * @param unknown $id            
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize);

    /**
     * 传送类型：
     * 消息：message/透传：quiet
     *
     * @param unknown $type            
     */
    public function getSendType($type);

    /**
     * 点击之后的打开行为
     *
     * @param string|[] $go_after
     *            go_app:打开app首页;
     *            go_custom:app自定义操作;
     *            go_url:打开url;
     *            go_page:打开指定界面,app需要提前定义
     */
    public function getAfterOpen($go_after);
}