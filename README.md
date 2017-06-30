# ronpearl/image-compress
Symfony package that will allow you to compress image files using Image Magick

**NOTE:** This is my first attempt at creating a package for packagist. Please feel free to submit any questions or concerns so I can address them accordingly.

## Getting Started
This program requires that you have PHP version 5.6.38 or higher and have Image Magick installed. You would need to confirm this in your phpinfo file, it would be listed under the section called "imagick". 

It also assumes that you are using composer and you have a composer.json file already. You can either add this to your require section:

```
    {
        "require": {
           "rpearl/image-compress": "1.*"
        }
    }
```

or you can run the composer command to install the package:

```
composer require pearl/image-compress
```

and then run:

```
composer update
```

## Usage
Once the package is installed, you can use it within your classes. Add the statement:

```
use ImageCompress\Compress;
```

This will then allow you to call the Compress class and start using the program. Here is a sample using an image URL:

```
$imageCompress = new Compress();
$compressionResults = $imageCompress->doImageCompression( "http://location/of/imagefile.jpg", FALSE);
dump( $compressionResults );
```

When creating an instance of the Compress() class, you can pass some variables to customize your compressions. The first variable would be an array of trusted domains (google.com, bing.com, etc...). This way you can limit what domain(s) any compressed image can originate from. The second and third are the compression values based upon Image Magick compression settings. They are based upon images that are either smaller than 800px wide or larger. Both have their own compression values. 

Here is a line showing the construction method for Compress():

```
public function __construct($trustedDomains = [], $smCompressionVal = 75, $lrgCompressionVal = 35)
```

Once this is set, you can call the doImageCompression() method. This is what allows you to one-by-one compress and receive values back for the final compressed file. The variables are ($originalFileUrlOrPath, $localFile = true, and $getBase64 = false). 

$originalFileUrlOrPath is the image URL or the local path to the image.

$localFile defaults to true, but this sets whether the image is from a URL or from a local file.

$getBase64 defaults to false, but you can set it to true if you would like to get the base64 results of the compressed file as well.

## Resulting Image(s)
The final images will be waved within your "web" directory under the folder "imageCompress". These folders will be created after your first run of the program. There will be 2 other folders under that: compressed and originals. The originals will temporarily hold the original files, and then the compressed images will be saved into the compressed folder for use later.

## Example
Youc an view the example.php file for a raw usage of the package.