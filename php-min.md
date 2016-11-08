 1. ClassName::class

自PHP 5.5，class关键字也可用于类名称解析。你可以通过使用类名::类包含的类名类的完全限定名的字符串。这是与命名空间类特别有用的。

``` stylus
namespace NS {
    class ClassName {
    }
    echo ClassName::class;
}
```
上面的例子将输出：

``` stylus
NS\ClassName
```


