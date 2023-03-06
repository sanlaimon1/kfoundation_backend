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

            const fileSelect = document.getElementById("fileSelect");
            const fileElem   = document.getElementById("fileElem");
            const fileList   = document.getElementById("fileList");
            const imgList    = document.getElementById("imgList");
            const list = document.createElement("ul");
            const img_arr = [];
            fileSelect.addEventListener("click", (e) => {

                if(fileElem){
                    fileElem.click();
                }
                e.preventDefault(); // prevent navigation to "#"
            });
            fileElem.addEventListener("change", handleFiles);

            function handleFiles(){
                // const lis = fileList.attr(src);
                // console.log(lis);
                let temp=imgList.children.length;
                // if(temp+this.files.length<=3){
                    var image = document.getElementById('showImg');
                    fileList.innerHTML = "";
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
                        img_arr.push(this.files[i].name);
                    }
                // }else{
                //     alert('You can upload only 5 images');
                // }
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

            $("#save_upload_file").click(function(){
                // const data = JSON.stringify(img_arr);
                img_arr.forEach(img => {
                    $.ajax({
                        url : "/save_image",
                        dataType : "json",
                        type: "POST",
                        data: {'data': img},
                        success: function(response){
                            console.log(response);
                            if(response){
                                location.reload();
                            }
                        }
                    });
                });
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
        <button class="btn btn-success mt-4" type="" id="save_upload_file">保存配置</button>
        
    </div>
    
</body>
</html>