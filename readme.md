## ImageX Flysystem

### 概述

ImageX 的 Flysystem 兼容层接口

### 准备

- 创建 ImageX 服务并且服务能够使用。
- 确保服务已经开启了原地址访问和上传任意文件功能。  
  这两个功能默认关闭，需要 [提工单](https://console.volcengine.com/ticket/createTicket/?step=3&ProviderName=%E5%9B%BE%E7%89%87%E6%9C%8D%E5%8A%A1ImageX&TemplateName=%E5%8A%9F%E8%83%BD%E9%85%8D%E7%BD%AE) 申请开启。  
  工单信息：
    - 问题类型：图片解决方案 / 功能配置
    - 问题描述：PHP框架源地址访问配置

### 配置

1. 通过 Composer 安装本库

    ```bash
    
        composer require exercisebook/flysystem-imagex
    
    ```

2. 创建 `ImageXAdapter` 对象

    ```php
    
    $imageX = new ImageXAdapter([
                                      'region' => 'Region',             // 如：cn-north-1
                                      'access_key' => 'Access Key',
                                      'secret_key' => 'Secret Key',
                                      'service_id' => 'Service ID',
                                      'domain' => 'Binding Domain'      // 填写该图片服务所绑定的域名如 imagex.superexercisebook.com。推荐以 https:// 开头。该值用来拼接 url。
                               ]);
    
    ```
