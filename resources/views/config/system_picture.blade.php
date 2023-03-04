<!DOCTYPE html>
<html lang="zh">
<head>
    <title>参数设置</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app .logo
        {
            width: 30px;
        }
        #app td
        {
            height: 30px;
            line-height: 30px;
        }
        ul li{
            display: inline;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function(){

            function webpConvert2($file, $compression_quality = 100)
            {
                console.log("Reach" , file_exists($file));
                // // check if file exists
                // if (!file_exists($file)) {
                //     return false;
                // }
                // $file_type = exif_imagetype($file);
                // $output_file =  $file . '.webp';
                
                // if (file_exists($output_file)) {
                //     return $output_file;
                // }
                // if (function_exists('imagewebp')) {
                //     switch ($file_type) {
                //         case '1': //IMAGETYPE_GIF
                //             $image = imagecreatefromgif($file);
                //             break;
                //         case '2': //IMAGETYPE_JPEG
                //             $image = imagecreatefromjpeg($file);
                //             break;
                //         case '3': //IMAGETYPE_PNG
                //                 $image = imagecreatefrompng($file);
                //                 imagepalettetotruecolor($image);
                //                 imagealphablending($image, true);
                //                 imagesavealpha($image, true);
                //                 break;
                //         case '6': // IMAGETYPE_BMP
                //             $image = imagecreatefrombmp($file);
                //             break;
                //         case '15': //IMAGETYPE_Webp
                //         return false;
                //             break;
                //         case '16': //IMAGETYPE_XBM
                //             $image = imagecreatefromxbm($file);
                //             break;
                //         default:
                //             return false;
                //     }
                //     // Save the image
                //     $result = imagewebp($image, $output_file, $compression_quality);
                //     if (false === $result) {
                //         return false;
                //     }
                //     // Free up memory
                //     imagedestroy($image);
                //     return $output_file;
                // } else if (class_exists('Imagick')) {
                //     $image = new Imagick();
                //     $image->readImage($file);
                //     if ($file_type === "3") {
                //         $image->setImageFormat('webp');
                //         $image->setImageCompressionQuality($compression_quality);
                //         $image->setOption('webp:lossless', 'true');
                //     }
                //     $image->writeImage($output_file);
                //     return $output_file;
                // }
                // return false;
            }

            const fileSelect = document.getElementById("fileSelect");
            const fileElem   = document.getElementById("fileElem");
            const fileList   = document.getElementById("fileList");
            const imgList    = document.getElementById("imgList");
            fileSelect.addEventListener("click", (e) => {

                if(fileElem){
                    fileElem.click();
                }
                e.preventDefault(); // prevent navigation to "#"
            });
            fileElem.addEventListener("change", handleFiles);

            function handleFiles(){
                let temp=imgList.children.length;
                if(temp+this.files.length<=5){
                    var image = document.getElementById('showImg');
                    // if(image == null){
                        fileList.innerHTML = "";
                        const list = document.createElement("ul");
                        fileList.appendChild(list);
                        for(let i=0;i<this.files.length;i++){
                            var li = document.createElement("li");
                            list.appendChild(li);
                            const img = document.createElement("img");
                            img.src = URL.createObjectURL(this.files[i]);
                            img.height = 120;
                            img.width = 120;
                            img.onload = () => {
                                URL.revokeObjectURL(img.src);
                            }
                            li.appendChild(img);
                        }
                }else{
                    alert('You can upload only 5 images');
                }
                $('#upload_submit_btn').removeClass('disabled');
            }

            function removeImg(){
                event.target.parentElement.remove();
                let imgElement_src=event.target.getAttribute('data-src');
            }
            function removePreviewImg(j){
                event.target.parentElement.remove();
                var img_array = [];
                var myFile = $('#fileElem').prop('files');
            }

            $('#upload_submit_btn').click(function(e){
                // var index = $('#fileElem').files; // to get the index, this index is 0 based
                var index = document.getElementById('fileElem');
                for(var i = 0; i < index.files.length; i++){
                    console.log(index.files[i].name);
                    const image_result = webpConvert2(index.files[i].name , 100)
                    console.log("Reach image result", image_result);
                }

            })
        });
    </script>

    <!-- include summernote css/js -->
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item active" aria-current="page">系统图片设置</li>
            </ol>
        </nav>
        
        <a href="" class="btn btn-info my-3" id="fileSelect">多图片上传</a>
        <input type="file" id="fileElem" class="fileElem" multiple accept="images/*" name="upload_new_file[]"  style="display: none"  />
        <div class="card mt-3" style="height:150">
            <p class="mx-2 mt-2">预览图：</p>
            <div id="fileList" class="previewImg py-2" >
            <ul id="imgList">
                <li id="li">
                    <img src="#" style="height: 120px; width: 120px;" id="showImg"/>
                    <a class="close" data-src="#" onclick="removeImg()" id="delete" >x</a>
                </li>
            </ul>
            </div>
        </div>
        <button class="btn btn-success mt-4 disabled" id="upload_submit_btn" type="button">保存配置</button>
    </div>
    
</body>
</html>