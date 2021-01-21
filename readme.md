# 概述

ImageX 的 Flysystem 兼容层接口

# 使用

1. 通过 Composer 安装本库

```bash

    composer require exercisebook/flysystem-imagex

```

2. 创建 `ImageXAdapter` 对象

```php

$imageX = new ImageXAdapter([
                                ‘region’ => 'Region',
                                ‘access_key‘ => 'Access Key',
                                ‘secret_key’ => 'Secret Key',
                                ‘service_id‘ => 'Service ID',
                                ‘domain’ => 'Binding Domain'
                            ]);

```
