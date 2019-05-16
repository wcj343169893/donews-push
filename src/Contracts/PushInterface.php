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
     * @param [] $after_open 点击后的效果
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
     *            打开app：go_app/自定义界面：go_custom/打开url:go_url
     */
    public function getAfterOpen($go_after);
}