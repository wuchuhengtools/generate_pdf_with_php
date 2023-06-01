## 1 使用说明

### 1.1 安装依赖
``` bash
$ composer install

```


### 1.2 导入字体
字体文件位于`public/assets/fonts`;
```bash
$ php vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i src/public/assets/fonts/MicrosoftYaHei.ttf   
```
导入成功后，会在`vendor/tecnickcom/tcpdf/fonts/`生相关的字体文件名，然后就可以使用字体了



### 1.3 生成pdf
```bash 
$ php src/main.php

```

然后在`src/runtime/`的目录下就有生成好的文件了。

## 1 Q&A

## 1.2 中文字体乱码问题

### 1.2.1 安装微软字体
下载完依赖后，进行字体安装
``` bash 
$  php vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i src/public/assets/fonts/MicrosoftYaHei.ttf   

>>> Converting fonts for TCPDF:
*** Output dir set to /www/vendor/tecnickcom/tcpdf/fonts/
+++ OK   : /www/src/public/assets/fonts/MicrosoftYaHei.ttf added as microsoftyahei
>>> Process successfully completed!
```

然后就可以直接在代码中使用了
``` php 
$this->setFont('microsoftyahei');

```

### 1.3 需要哪些扩展

* gd
* zip

## 2 如何导入*PingFang-SC-Light`字体?

```bash
$  php vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i src/public/assets/fonts/PingFang-SC-Light.ttf  

>>> Converting fonts for TCPDF:
*** Output dir set to /www/vendor/tecnickcom/tcpdf/fonts/
+++ OK   : /www/src/public/assets/fonts/PingFang-SC-Light.ttf added as pingfangsclight
>>> Process successfully completed!

```

然后在*TCPDF*中修改要使用的字体

**main.php**
``` bash
<?php

// ...

class MYPDF extends TCPDF {
    public $_sideMargin= 10;

    // 表格边框style
    public  $tableBorder = ['LTRB' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => [189, 189, 189])];

    // 微软雅黑字体: 这个手动导入，详细说明看README.md
    public $font= 'pingfangsclight';
    // ...
    }
```

> 字体pingfangSCLight来源 [github.com/zongren/font](https://github.com/zongren/font)

> 字体[github.com/adobe-fonts/source-han-sans/releases](https://github.com/be5invis/source-han-sans-ttf/releases)来源

下载字体后是*ttc*格式，然后在[字体转换网站](https://transfonter.org/ttc-unpack)进行转为*ttf*,并选择其中需要的*ttf*下载下来使用
