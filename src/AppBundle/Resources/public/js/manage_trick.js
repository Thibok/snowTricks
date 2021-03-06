$(function () {
    const previewThumbPath = "/img/preview_trick_thumb.jpg";
    const previewLargePath = "/img/preview_trick.jpg";
    const editIconPath = "/img/edit.png";
    const deleteIconPath = "/img/delete.png";
    const nextEnabledPath = "/img/next.png";
    const prevEnabledPath = "/img/prev.png";
    const nextDisableddPath = "/img/next_disabled.png";
    const prevDisabledPath = "/img/prev_disabled.png";
    const favIconPath = "/img/fav.png";
    const localImgSrcRegex = new RegExp("^\/uploads\/img\/trick\/[a-z0-9.-]+$");
    const youtubeEmbedFormat = "https://www.youtube.com/embed/";
    const dailymotionEmbedFormat = "https://www.dailymotion.com/embed/video/";
    const vimeoEmbedFormat = "https://player.vimeo.com/video/";
    const iframeRegex = new RegExp("^<iframe .*><\/iframe>$");
    const videoUrlSrcRegex = /src\s*=\s*"(.+?)"/;
    const urlRegex = new RegExp("^(https\:\/\/){1}(www\.youtube\.com\/embed\/[a-zA-Z0-9\?\=\&_-]{1,2053}|www\.dailymotion\.com\/embed\/video\/[a-zA-Z0-9\?\=\&_-]{1,2043}|player\.vimeo\.com\/video\/[a-zA-Z0-9\?\=\#\&_-]{1,2052}|youtu\.be\/[a-zA-Z0-9\?\=\&_-]{1,2066}|www\.youtube\.com\/watch\\?v\=[a-zA-Z0-9\?\=\&_-]{1,2051}|www\.dailymotion\.com\/video\/[a-zA-Z0-9\?\=\&_-]{1,2049}|dai\.ly\/[a-zA-Z0-9\?\=\&_-]{1,2068}|vimeo\.com\/[a-zA-Z0-9\?\=\#\&_-]{1,2065})$");
    const urlEmbedRegex = new RegExp("^(https\:\/\/){1}(www\.youtube\.com\/embed\/[a-zA-Z0-9\?\=\&_-]{1,2053}|www\.dailymotion\.com\/embed\/video\/[a-zA-Z0-9\?\=\&_-]{1,2043}|player\.vimeo\.com\/video\/[a-zA-Z0-9\?\=\#\&_-]{1,2052})$");
    const linksRegex = new RegExp("^(https\:\/\/){1}(www\.youtube\.com\/watch\\?v\=[a-zA-Z0-9\?\=\&_-]{1,2051}|youtu\.be\/[a-zA-Z0-9\?\=\&_-]{1,2066}|www\.dailymotion\.com\/video\/[a-zA-Z0-9\?\=\&_-]{1,2049}|dai\.ly\/[a-zA-Z0-9\?\=\&_-]{1,2068}|vimeo\.com\/[a-zA-Z0-9\?\=\#\&_-]{1,2065})$");
    const youtubeRegex = new RegExp("^(https\:\/\/){1}(www\.youtube\.com\/watch\\?v\=[a-zA-Z0-9\?\=\&_-]{1,2051})$");
    const youtubeShortRegex = new RegExp("^(https\:\/\/){1}(youtu\.be\/[a-zA-Z0-9\?\=\&_-]{1,2066})$");
    const vimeoRegex = new RegExp("^(https\:\/\/){1}(vimeo\.com\/[a-zA-Z0-9\?\=\#\&_-]{1,2065})$");
    const dailymotionRegex = new RegExp("^(https\:\/\/){1}(www\.dailymotion\.com\/video\/[a-zA-Z0-9\?\=\&_-]{1,2049})$");
    const dailymotionShortRegex = new RegExp("^(https\:\/\/){1}(dai\.ly\/[a-zA-Z0-9\?\=\&_-]{1,2068})$");
    const videoUrlMaxLength = 2083;
    const minLengthMessage = "must be at least";
    const maxLengthMessage = "must be at most";
    const allowedFileExtension = ["jpg", "jpeg", "png"];
    const nameMinLength = 2;
    const nameMaxLength = 60;
    const nameRegex = new RegExp("^([a-zA-Z0-9]+ ?[a-zA-Z0-9]+)+$");
    const descriptionMaxLength = 3000;
    const descriptionRegex = new RegExp("[<>]");

    var name = $("#appbundle_trick_name");
    var description = $("#appbundle_trick_description");
    var actualPage = 1;
    var totalMedias = 0;
    var containerImages = $("#trickImages");
    var containerVideos = $("#trickVideos");
    $("#see_media_container").hide();
    var seeMedia = false;
    var imagesLength = 0;
    var videosLength = 0;

    function getMediaPerPage() {
        if (window.innerWidth < 1609 && window.innerWidth > 1299 || window.innerWidth < 992  && window.innerWidth > 944) {
            return 4;
        }

        if (window.innerWidth < 1300 && window.innerWidth > 1154 || window.innerWidth < 945  && window.innerWidth > 815) {
            return 3;
        }

        if (window.innerWidth < 1155 && window.innerWidth > 991 || window.innerWidth < 816  && window.innerWidth > 590) {
            return 2;
        }

        if (window.innerWidth < 591){
            return 1;
        }

        return 5;
    }

    function getTotalPages() {
        var totalPages = Math.ceil(totalMedias / getMediaPerPage());

        if (totalPages === 0) {
            return 1;
        }

        return totalPages;
    }

    function readThumbURL(input) {
        if (input.files && input.files[0]) {
            let thumbReader = new FileReader();

            thumbReader.onload = function(e) {
                let idSplit = $(input).attr("id").split("_");
                $("#trick-img-thumb-" + idSplit[3]).attr("src", e.target.result);
            }

            thumbReader.readAsDataURL(input.files[0]);
        }

        return;
    }

    function readMainUrl(input) {
        if (input.files && input.files[0]) {
            if ($(input).hasClass("fav-input")) {
                var mainReader = new FileReader();

                mainReader.onload = function(e) {

                    var img = document.createElement("img");
                    img.src = e.target.result;

                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0);

                    var width = 1200;
                    var height = 500;

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    let dataurl = canvas.toDataURL(input.files[0].type);
                    $("#mainTrickImg").attr("src", dataurl);
                }
                    
                mainReader.readAsDataURL(input.files[0]);
            }
        }

        return;
    }

    function loadDefaultThumbImgPreview (input) {
        let idSplit = input.attr("id").split("_");
        $("#trick-img-thumb-" + idSplit[3]).attr("src", previewThumbPath);

        if ($(input).hasClass("fav-input")) {
            $("#mainTrickImg").attr("src", previewLargePath);
        }
    }

    function deleteTrickImage(imageId) {
        $("#img-container-" + imageId).remove();
        $("#appbundle_trick_images_" + imageId +"_file").remove();
    }

    function refreshImages() {
        let inputsFile = containerImages.children(":input");
        let inputsFileLength = inputsFile.length;

        if (inputsFileLength === 0) {
            imagesLength  = 0;
            return;
        }

        imagesLength = 0;

        while(imagesLength < inputsFileLength) {
            let mediaId = "img-container-" + imagesLength;
            let mediaImgId = "trick-img-thumb-" + imagesLength;
            let counterId = "img-number-" + imagesLength;
            let editControlId = "edit-img-" + imagesLength;
            let deleteControlId = "delete-img-" + imagesLength;
            let inputId = "appbundle_trick_images_" + imagesLength + "_file";
            let inputName = "appbundle_trick[images][" + imagesLength + "][file]";

            $(".image").eq(imagesLength).attr("id", mediaId);
            $(".media-img").eq(imagesLength).attr("id", mediaImgId);
            $(".counter-img").eq(imagesLength).attr("id", counterId).text(imagesLength + 1);
            $(".edit-control-img").eq(imagesLength).attr("id", editControlId);
            $(".delete-control-img").eq(imagesLength).attr("id", deleteControlId);
            inputsFile.eq(imagesLength).attr("id", inputId);

            imagesLength++;
        }

        return;
    }

    function getTotalMedias() {
        return $(".media").length;
    }

    function disableNext() {
        $("#next").removeAttr("href");
        $("#next img").attr("src", nextDisableddPath);
        return;
    }

    function disablePrev() {
        $("#previous").removeAttr("href");
        $("#previous img").attr("src", prevDisabledPath);
        return;
    }

    function enableNext() {
        $("#next").attr("href", "#");
        $("#next img").attr("src", nextEnabledPath);
        return;
    }

    function enablePrev() {
        $("#previous").attr("href", "#");
        $("#previous img").attr("src", prevEnabledPath);
        return;
    }

    function canPrev() {
        if (actualPage > 1) {
            return true;
        }

        return false;
    }

    function canNext() {
        if (actualPage < getTotalPages()) {
            return true;
        }

        return false;
    }

    function showPagination() {

        if (window.innerWidth < 591 && seeMedia === false) {
            $("#pagination").hide();
            return;
        }
        
        if (getTotalMedias() <= getMediaPerPage()) {
            disableNext();
            disablePrev();
            $("#pagination").hide();
            return;
        }

        if (getTotalMedias() > getMediaPerPage()) {
            $("#pagination").show();

            if (canNext()) {
                enableNext();
            } else {
                disableNext();
            }

            if (canPrev()) {
                enablePrev();
            } else {
                disablePrev();
            }

            return;
        }
    }

    function refreshPagination() {
        $(".reveal-media").each(function () {
            $(this).removeClass("reveal-media d-inline-block");
            $(this).hide();
        })

        if (getMediaPerPage() === 1) {
            $(".media").eq(actualPage - 1).addClass("reveal-media d-inline-block").show();
            showPagination();
            return;
        }

        var lastPos = actualPage * getMediaPerPage();
        var firstPos = lastPos - getMediaPerPage();

        while(firstPos < lastPos) {
            $(".media").eq(firstPos).addClass("reveal-media d-inline-block").show();
            firstPos++;
        }

        showPagination();

        return;
    }

    function getNbOfMediaActualPage() {
        return $(".reveal-media").length;
    }

    function pageNext() {
        $(".reveal-media").each(function () {
            $(this).removeClass("reveal-media d-inline-block");
            $(this).hide();
        })

        actualPage++;

        if (!canNext()) {
            disableNext();
        } else {
            enableNext();
        }

        if (!canPrev()) {
            disablePrev();
        } else {
            enablePrev();
        }

        if (getMediaPerPage() === 1) {
            $(".media").eq(actualPage - 1).addClass("reveal-media d-inline-block").show();
            return;
        }

        var lastPos = actualPage * getMediaPerPage();
        var firstPos = lastPos - getMediaPerPage();

        while(firstPos < lastPos) {
            $(".media").eq(firstPos).addClass("reveal-media d-inline-block").show();
            firstPos++;
        }

        return;      
    }

    function pagePrevious() {
        $(".reveal-media").each(function () {
            $(this).removeClass("reveal-media d-inline-block");
            $(this).hide();
        })

        actualPage--;

        if (!canNext()) {
            disableNext();
        } else {
            enableNext();
        }

        if (!canPrev()) {
            disablePrev();
        } else {
            enablePrev();
        }

        if (getMediaPerPage() === 1) {
            $(".media").eq(actualPage - 1).addClass("reveal-media d-inline-block").show();
            return;
        }

        var lastPos = actualPage * getMediaPerPage();
        var firstPos = lastPos - getMediaPerPage();

        while(firstPos < lastPos) {
            $(".media").eq(firstPos).addClass("reveal-media d-inline-block").show();
            firstPos++;
        }

        return;
    }

    function createControlsImage() {
        let controlsContainer = $("<div class='d-flex justify-content-around mt-2 border-black controls-container'></div>");

        let editImg = $("<img src='" + editIconPath + "' alt='Edit image icon'/>").css("height", "16px").css("width", "16px");
        let deleteImg = $("<img src='" + deleteIconPath + "' alt='Delete image icon'/>").css("height", "16px").css("width", "16px");

        let editLink = $("<a id='edit-img-" + imagesLength + "' class='edit-control-img' href='#'></a>");
        let deleteLink = $("<a id='delete-img-" + imagesLength + "' class='delete-control-img' href='#'></a>");

        editLink.append(editImg);
        deleteLink.append(deleteImg);
        
        let numberOfImg = $("<span class='counter-img' id='img-number-" + imagesLength + "'></span>");
        numberOfImg.css("margin-top", "2px");
        numberOfImg.text(imagesLength + 1);

        $(editLink).click(function (e) {
            e.preventDefault();
            let idSplit = $(this).attr("id").split("-");
            $("#appbundle_trick_images_" + idSplit[2] +"_file").trigger("click");
    
            return false;
        });

        $(deleteLink).click(function (e) {
            e.preventDefault();
            let idSplit = $(this).attr("id").split("-");

            deleteTrickImage(idSplit[2]);
            totalMedias--;

            refreshImages();
            refreshPagination();

            if ($(".fav").length === 0) {
                let newMainImg = $("#img-container-0");
                let src = newMainImg.children("img").attr("src");
                let favImg = $("<img/>").attr("src", favIconPath).attr("alt", "Favorite check image icon").addClass("fav-check");
                favImg.css("width", "16px").css("height", "16px").css("margin-top", "6px");
                $("#edit-img-0").before(favImg);
                newMainImg.addClass("fav");
                let inputFile = document.getElementById("appbundle_trick_images_0_file");
                if (inputFile !== null) {
                    inputFile.className += " fav-input";
                }
    
                if (newMainImg.hasClass("exist") && localImgSrcRegex.test(src)) {
                    let splitSrc = src.split("thumb-");
                    let newSrc = splitSrc[0] + splitSrc[1];
                    $("#mainTrickImg").attr("src", newSrc);
                } else if (src !== undefined && src !== previewThumbPath && !localImgSrcRegex.test(src)) {
                    readMainUrl(inputFile);
                } else {
                    $("#mainTrickImg").attr("src", previewLargePath);
                }

                $(".delete-main-trick-img").attr("id", "delete-main-0");
                $(".edit-main-trick-img").attr("id", "edit-main-0");
            }

            if (getNbOfMediaActualPage() === 0) {
                if (canPrev()) {
                    pagePrevious();
                }
            }
    
            return false;
        });

        controlsContainer.append(numberOfImg);

        let favImg;

        if (imagesLength === 0) {
            favImg = $("<img src='" + favIconPath + "' alt='Favorite check image icon'/>").css("height", "16px").css("width", "16px").css("margin-top", "6px");
            controlsContainer.append(favImg);
        }

        controlsContainer.append(editLink);
        controlsContainer.append(deleteLink);

        return controlsContainer;
    }

    function validateImage(image) {
        let val = image.val();
        let imageId = image.attr("id").split("_");
        imageId = Number(imageId[3]) + 1;

        if (val.length === 0) {
            $("#media_error").text("");
            return true;
        }

        if ($.inArray(val.split(".").pop().toLowerCase(), allowedFileExtension) === -1) {
            $("#media_error").text("Image " + imageId + " : Allowed extensions : jpg, jpeg, png");
            return false;
        }

        $("#media_error").text("");
        return true;
    }

    function recreateImages() {
        if (imagesLength === 0) {
            $(".edit-main-trick-img").attr("id", "edit-main-0");
            $(".delete-main-trick-img").attr("id", "delete-main-0");
        }

        var fileInputs = containerImages.children(":input");
        var fileInputsLength = fileInputs.length;

        if (fileInputsLength === 0) {
            return;
        }

        fileInputs.eq(0).addClass("fav-input");
        fileInputs.hide();
        let firstPrevImg = $("#img-container-0");

        while(imagesLength < fileInputsLength) {
            
            if (imagesLength === 0 && firstPrevImg.hasClass("exist")) {
                let prevSrc = firstPrevImg.children("img").attr("src");
                let splitSrc = prevSrc.split("thumb-");
                let newSrc = splitSrc[0] + splitSrc[1];
                $("#mainTrickImg").attr("src", newSrc);
            }
            
            let fileInputId = "appbundle_trick_images_" + imagesLength + "_file";
            let fileInputName = "appbundle_trick[images][" + imagesLength + "][file]";
            
            let fileInput = fileInputs.eq(imagesLength).attr("id", fileInputId).attr("name", fileInputName);

            fileInput.change(function () {
                if ($(this).val().length !== 0 && validateImage($(this))) {
                    readThumbURL(this);
                    if ($(this).hasClass("fav-input")) {
                        readMainUrl(this);
                    }
                } else {
                    loadDefaultThumbImgPreview($(this));
                }
            });

            let spanServerError = fileInput.next("span");
            let errorMsg = spanServerError.find(".form-error-message").text();
            if (errorMsg.length !== 0) {
                errorMsg = "Image " + (imagesLength + 1) + " : " + errorMsg;
                $("#media_error").text(errorMsg);
                spanServerError.remove();
            }

            let imgPreview = $(".image").eq(imagesLength);

            let controls = createControlsImage();
            imgPreview.append(controls);

            if (getNbOfMediaActualPage() < getMediaPerPage()) {
                imgPreview.addClass("reveal-media");
            } else {
                imgPreview.removeClass("d-inline-block");
                imgPreview.hide();
            }        
                    
            imagesLength++;
            totalMedias++;
        }

        return;
    }

    function validateEmbedUrl(url, errorTarget) {
        if (url.length === 0) {
            let errorMsg = "Url of the video can not be empty !";

            errorTarget.text(errorMsg);
            return false;
        }

        if (url.length > videoUrlMaxLength) {
            let errorMsg = "Url of the video " + maxLengthMessage + " " + videoUrlMaxLength + " characters !"

            errorTarget.text(errorMsg);
            return false;
        }

        if (!urlEmbedRegex.test(url)) {
            let errorMsg = "The integration tag must come from Youtube, Dailymotion or Vimeo";

            errorTarget.text(errorMsg);
            return false;
        }

        $(errorTarget).text("");
        return true;
    }

    function validateIframe(iframe, errorTarget) {
        if (!iframeRegex.test(iframe)) {
            errorTarget.text("Please enter a valid iframe tag");
            return false;
        }

        let src = videoUrlSrcRegex.exec(iframe);

        if (src === null) {
            errorTarget.text("The iframe tag must contain a valid src link");
            return false;
        }

        let url = src[0].split("\"");

        if (!validateEmbedUrl(url[1], errorTarget)) {
            return false;
        }

        errorTarget.text("");
        return true;
    }

    var addVideoModal = new jBox("Confirm", {
        title: "Add a video",
        cancelButton: "Cancel",
        confirmButton: "Upload",
        confirm: uploadVideo,
    });

    function createAddVideoIframeModal() {
        let modalContainer = $("<div class='modalContainer'></div>");
        let labelModalUrl = $("<label id='addVideoLabel' class='control-label required' for='addVideo'>Iframe</label>");
        let textareaModalUrl = $("<textarea id='addVideo' required='required' class='form-control'></textarea>")
        let errorModalUrl = $("<span class='invalid-feedback d-block' id='add_video_error'></span>");

        textareaModalUrl.on("keyup blur", function () {
            validateIframe($(this).val(), $("#add_video_error"));
        });

        modalContainer.append(labelModalUrl);
        modalContainer.append(textareaModalUrl);
        modalContainer.append(errorModalUrl);

        addVideoModal.setContent(modalContainer);

        return;
    }

    function validateLink(link, errorTarget) {
        if (link.length === 0) {
            let errorMsg = "You must enter a link !";

            errorTarget.text(errorMsg);
            return false;
        }

        if (link.length > videoUrlMaxLength) {
            let errorMsg = "The link " + maxLengthMessage + " " + videoUrlMaxLength + " characters !"

            errorTarget.text(errorMsg);
            return false;
        }

        if (!linksRegex.test(link)) {
            let errorMsg = "The link must come from Youtube, Dailymotion or Vimeo";

            errorTarget.text(errorMsg);
            return false;
        }

        errorTarget.text("");
        return true;
    }

    function createAddVideoLinkModal() {
        let modalContainer = $("<div class='modalContainer'></div>");
        let labelModalUrl = $("<label id='addVideoLabel' class='control-label required' for='addVideo'>Link</label>");
        let textareaModalUrl = $("<textarea id='addVideo' required='required' class='form-control'></textarea>")
        let errorModalUrl = $("<span class='invalid-feedback d-block' id='add_video_error'></span>");

        textareaModalUrl.on("keyup blur", function () {
            validateLink($(this).val(), $("#add_video_error"));
        });

        modalContainer.append(labelModalUrl);
        modalContainer.append(textareaModalUrl);
        modalContainer.append(errorModalUrl);

        addVideoModal.setContent(modalContainer);

        return;
    }

    function createAddVideoByModalContent() {
        let addVideoContainer = $("<ul></ul>");
        let videoIframeLi = $("<li></li>").css("list-style-type", "none");
        let videoLinkLi = $("<li></li>").css("list-style-type", "none");
        let videoIframeLink = $("<a href='#'>Add video with embed tag</a>");
        let videoLink = $("<a href='#'>Add video with link</a>");

        videoIframeLink.click(function (e) {
            e.preventDefault();
            addVideoByModal.close();
            createAddVideoIframeModal();
            addVideoModal.open();
        });

        videoLink.click(function (e) {
            e.preventDefault();
            addVideoByModal.close();
            createAddVideoLinkModal()
            addVideoModal.open();
        });

        videoIframeLi.append(videoIframeLink);
        videoLinkLi.append(videoLink);

        addVideoContainer.append(videoIframeLi);
        addVideoContainer.append(videoLinkLi);

        return addVideoContainer;
    }

    function createVideoIframe (videoSrc) {
        let iframeVideoEl = "<iframe class='align-middle' width=100% height=100% " + videoSrc + " frameborder='0'></iframe>";

        return iframeVideoEl;
    }

    function convertLinkToEmbed(link) {
        if (youtubeRegex.test(link)) {
            let videoCode = link.split("v=");
            let embedLink = youtubeEmbedFormat + videoCode[1];

            return embedLink;
        }

        if (youtubeShortRegex.test(link)) {
            let videoCode = link.split("/");
            let embedLink = youtubeEmbedFormat + videoCode[3];
            
            return embedLink;
        }

        if (dailymotionRegex.test(link)) {
            let videoCode = link.split("/");
            let embedLink = dailymotionEmbedFormat + videoCode[4];

            return embedLink;
        }

        if (dailymotionShortRegex.test(link)) {
            let videoCode = link.split("/");
            let embedLink = dailymotionEmbedFormat + videoCode[3];

            return embedLink;
        }

        if (vimeoRegex.test(link)) {
            let videoCode = link.split("/");
            let embedLink = vimeoEmbedFormat + videoCode[3];

            return embedLink;
        }
    }

    function editVideo() {
        let videoValue  = $("#editVideo").val();
        let titleSplit = $("#editModalTitle").text().split(" ");
        let videoId = Number(titleSplit[3]) - 1;

        if ($("#labelEditVideo").text() === "Iframe") {
            if(!validateIframe(videoValue, $("#video_edit_error"))) {
                return;
            }

            let src = videoUrlSrcRegex.exec(videoValue);
            let url = src[0].split("\"");

            let videoIframePreview = $("#video-container-" + videoId + " iframe");
            videoIframePreview.attr("src", url[1]);

            let videoUrlInput = $("#appbundle_trick_videos_" + videoId + "_url");
            videoUrlInput.val(url[1]);
            videoUrlInput.change();
        } else {
            if (!validateLink(videoValue, $("#video_edit_error"))) {
                return;
            }

            let embedLink = convertLinkToEmbed(videoValue);

            let videoIframePreview = $("#video-container-" + videoId + " iframe");
            videoIframePreview.attr("src", embedLink);

            let videoUrlInput = $("#appbundle_trick_videos_" + videoId + "_url");
            videoUrlInput.val(videoValue);
            videoUrlInput.change();
        }

        return;
    }

    var editVideoModal = new jBox("Confirm", {
        cancelButton: "Cancel",
        confirmButton: "Edit",
        confirm: editVideo,
    });

    function createEditVideoModal(videoId) {       
        let modalEditContainer = $("<div class='modalContainer'></div>");
        let numberTitle = Number(videoId) + 1;
        let modalEditTitle = "<h6 id='editModalTitle'>Edit video - " + numberTitle + "</h6>";
        let labelModalEditUrl = $("<label id='labelEditVideo' class='control-label required' for='editVideo'></label>");
        let textareaModalEditUrl = $("<textarea id='editVideo' required='required' class='form-control'></textarea>");
        let errorModalEditUrl = $("<span class='invalid-feedback d-block' id='video_edit_error'></span>");

        let videoValue = $("#appbundle_trick_videos_" + videoId +"_url").val();

        if (urlEmbedRegex.test(videoValue)) {
            labelModalEditUrl.text("Iframe");
            var src = "src='" + videoValue + "'";
            videoValue = createVideoIframe(src);

            textareaModalEditUrl.on("keyup blur", function () {
                validateIframe($(this).val(), $("#video_edit_error"));
            });
        } else {
            labelModalEditUrl.text("Link");
            textareaModalEditUrl.on("keyup blur", function () {
                validateLink($(this).val(), $("#video_edit_error"));
            });
        }

        textareaModalEditUrl.val(videoValue);

        modalEditContainer.append(labelModalEditUrl);
        modalEditContainer.append(textareaModalEditUrl);
        modalEditContainer.append(errorModalEditUrl);

        editVideoModal.setTitle(modalEditTitle);
        editVideoModal.setContent(modalEditContainer);

        return editVideoModal;
    }

    function deleteTrickVideo(idNumber) {
        $("#video-container-" + idNumber).remove();
        $("#appbundle_trick_videos_" + idNumber +"_url").remove();
    }
    
    function refreshVideos() {
        let inputsUrl = containerVideos.children(":input");
        let inputsUrlLength = inputsUrl.length;

        if (inputsUrlLength === 0) {
            videosLength  = 0;
            return;
        }

        videosLength = 0;

        while(videosLength < inputsUrlLength) {
            let mediaId = "video-container-" + videosLength;
            let counterId = "video-number-" + videosLength;
            let editControlId = "edit-video-" + videosLength;
            let deleteControlId = "delete-video-" + videosLength;
            let inputId = "appbundle_trick_videos_" + videosLength + "_url";

            $(".video").eq(videosLength).attr("id", mediaId);
            $(".counter-video").eq(videosLength).attr("id", counterId).text(videosLength + 1);
            $(".edit-control-video").eq(videosLength).attr("id", editControlId);
            $(".delete-control-video").eq(videosLength).attr("id", deleteControlId);
            inputsUrl.eq(videosLength).attr("id", inputId);

            videosLength++;
        }

        return;
    }

    function createControlsVideo () {
        let controlsContainer = $("<div class='d-flex flex-row justify-content-around mt-2 border-black controls-container'></div>");
        
        let editImg = $("<img src='" + editIconPath + "' alt='Edit video icon'/>").css("height", "16px").css("width", "16px");
        let deleteImg = $("<img src='" + deleteIconPath + "' alt='Delete video icon'/>").css("height", "16px").css("width", "16px");

        let editLink = $("<a id='edit-video-" + videosLength + "' class='edit-control-video px-1' href='#'></a>");
        let deleteLink = $("<a id='delete-video-" + videosLength + "' class='delete-control-video px-1' href='#'></a>");

        let numberOfVideos = $("<span class='counter-video px-1' id='video-number-" + videosLength + "'></span>");
        numberOfVideos.css("margin-top", "2px");
        numberOfVideos.text(videosLength + 1);

        $(editLink).click(function (e) {
            e.preventDefault();
            let idSplit = $(this).attr("id").split("-");
            let editModal = createEditVideoModal(idSplit[2]);
            editModal.open();
        });

        $(deleteLink).click(function (e) {
            e.preventDefault();
            let idSplit = $(this).attr("id").split("-");
            deleteTrickVideo(idSplit[2]);
            totalMedias--;

            refreshVideos();
            refreshPagination();

            if (getNbOfMediaActualPage() === 0) {
                if (canPrev()) {
                    pagePrevious();
                }
            }
    
            return false;
        });

        editLink.append(editImg);
        deleteLink.append(deleteImg);

        controlsContainer.append(numberOfVideos);
        controlsContainer.append(editLink);
        controlsContainer.append(deleteLink);

        return controlsContainer;
    }

    function createVideoEl (videoSrc) {
        let videoContainerEl = $("<div id='video-container-" + videosLength + "' class='d-inline-block mt-1 mb-5 media video'></div>");
        videoContainerEl.css("height", "100px");
        let videoIframeEl = createVideoIframe(videoSrc);
        let controlsEl = createControlsVideo();

        videoContainerEl.append($(videoIframeEl));
        videoContainerEl.append(controlsEl);

        return videoContainerEl;
    }

    function validateUrl(url, videoId) {
        if (url.length === 0) {
            let errorMsg = "Url of the video can not be empty !";
            errorMsg = "Video " + videoId + " : " + errorMsg;
            

            $("#media_error").text(errorMsg);
            return false;
        }

        if (url.length > videoUrlMaxLength) {
            let errorMsg = "Url of the video " + maxLengthMessage + " " + videoUrlMaxLength + " characters !"
            errorMsg = "Video " + videoId + " : " + errorMsg;

            $("#media_error").text(errorMsg);
            return false;
        }

        if (!urlRegex.test(url)) {
            let errorMsg = "The video must come from Youtube, Dailymotion or Vimeo";
            errorMsg = "Video " + videoId + " : " + errorMsg;

            $("#media_error").text(errorMsg);
            return false;
        }

        $("#media_error").text("");
        return true;
    }

    function createInputVideoUrl() {
        let prototype = containerVideos.attr("data-prototype").replace(/__name__/g, videosLength);
        let urlField = $(prototype);

        urlField.change(function () {
            let idSplit = $(this).attr("id").split("_");
            validateUrl($(this).val(), Number(idSplit[3]) + 1);
        });

        return urlField;
    }

    function uploadVideo() {
        let videoValue  = $("#addVideo").val();

        let videoEl;
        let urlField;

        if ($("#addVideoLabel").text() === "Iframe") {
            if (!validateIframe(videoValue, $("#add_video_error"))) {
                return;
            }

            let src = videoUrlSrcRegex.exec(videoValue);
            videoEl = createVideoEl(src[0]);
            urlField = createInputVideoUrl();
            let url = src[0].split("\"");

            urlField.val(url[1]);
        } else {
            if (!validateLink(videoValue, $("#add_video_error"))) {
                return;
            }

            let embedLink = convertLinkToEmbed(videoValue);
            let src = "src='" + embedLink + "'";
            videoEl = createVideoEl(src);
            urlField = createInputVideoUrl();
            urlField.val(videoValue);
        }

        urlField.hide();

        if (getNbOfMediaActualPage() < getMediaPerPage()) {
            videoEl.addClass("reveal-media");
        } else {
            videoEl.removeClass("d-inline-block");
            videoEl.hide();
        }   

        containerVideos.append(urlField);
        $("#medias_container").append(videoEl);

        urlField.change();
        videosLength++;
        totalMedias++;

        refreshPagination();
    }

    var addVideoByModalContent = createAddVideoByModalContent();

    var addVideoByModal = new jBox("Modal", {
        title: "Add a video",
        content: addVideoByModalContent,
    });

    function goToDeleteTrickPage() {
        let url = $("#deleteTrickBtn").attr("href");
        $(location).attr("href", url);
    }

    function recreateVideos() {
        let videosInputs = containerVideos.children(":input");
        let videosInputsLength = videosInputs.length;

        videosInputs.hide();

        if (videosInputsLength === 0) {
            return;
        }

        while (videosLength < videosInputsLength) {
            let videoInputId = "appbundle_trick_videos_" + videosLength + "_url";
            let videoInputName = "appbundle_trick[videos][" + videosLength + "][url]";

            let videoInput = videosInputs.eq(videosLength);
            videoInput.attr("id", videoInputId).attr("name", videoInputName);

            var videoUrl = videoInput.val();

            if (linksRegex.test(videoUrl)) {
                var videoUrl = convertLinkToEmbed(videoUrl);
            }

            let srcVideo = "src='" + videoUrl + "'";

            let spanServerError = videoInput.next("span");
            let errorMsg = spanServerError.find(".form-error-message").text();
            if (errorMsg.length !== 0) {
                errorMsg = "Video " + (videosLength + 1) + " : " + errorMsg;
                $("#media_error").text(errorMsg);
                spanServerError.remove();
            }

            let videoEl = createVideoEl(srcVideo);

            if (getNbOfMediaActualPage() < getMediaPerPage()) {
                videoEl.addClass("reveal-media");
            } else {
                videoEl.removeClass("d-inline-block");
                videoEl.hide();
            }

            $("#medias_container").append(videoEl);

            videosLength++;
            totalMedias++;
        }
    }

    recreateImages();
    recreateVideos();
    showPagination();

    var deleteTrickModal = new jBox("Confirm", {
        cancelButton: "Cancel",
        confirmButton: "Delete",
        content: "Are you sure you want to do that ?",
        confirm: goToDeleteTrickPage,
    });

    function createImageEl() {
        var previewImgContainer = $("<div id='img-container-" + imagesLength + "' class='d-inline-block mt-1 mb-5 media image'></div>");
        if (imagesLength === 0) {
            previewImgContainer.addClass("fav");
        }
        var previewImg = $("<img class='border-black media-img' id='trick-img-thumb-" + imagesLength + "' src='"+ previewThumbPath +"' alt='Thumb image'/>");

        var controlsEl = createControlsImage();

        previewImgContainer.append(previewImg);
        previewImgContainer.append(controlsEl);

        return previewImgContainer;
    }

    function createInputImageFile() {
        let prototype = containerImages.attr("data-prototype").replace(/__name__/g, imagesLength);
        let fileField = $(prototype);

        if (imagesLength === 0) {
            fileField.addClass("fav-input");
            $(".edit-main-trick-img").attr("id", "edit-main-" + imagesLength);
            $(".delete-main-trick-img").attr("id", "delete-main-" + imagesLength);
        }

        fileField.change(function () {
            if ($(this).val().length !== 0 && validateImage($(this))) {
                readThumbURL(this);
                if ($(this).hasClass("fav-input")) {
                    readMainUrl(this);
                }
            } else {
                loadDefaultThumbImgPreview($(this));
            }
        });

        return fileField;
    }

    function uploadImage() {
        // Get html prototype 
        let inputFile = createInputImageFile();
        inputFile.hide();
        
        containerImages.append(inputFile);

        let imagePreview = createImageEl();

        if (getNbOfMediaActualPage() < getMediaPerPage()) {
            imagePreview.addClass("reveal-media");
        } else {
            imagePreview.removeClass("d-inline-block");
            imagePreview.hide();
        }        

        if (videosLength === 0) {
            $("#medias_container").append(imagePreview);
        } else {
            $(".video").eq(0).before(imagePreview);
        }
        
        imagesLength++;
        totalMedias++;

        refreshPagination();

        inputFile.trigger("click");

        if (window.innerWidth < 591) {
            if (seeMedia === false) {
                $("#medias_container").hide();
                $("#media_error_container").hide();

                if ($(".media").length > 0) {
                    $("#see_media_container").show();
                } else {
                    $("#see_media_container").hide();
                }
            }
        }
    }

    function validateName(name) {
        if (name.length < nameMinLength) {
            $("#name_error").text("The name " + minLengthMessage + " " + nameMinLength + " characters !");
            return false;
        }

        if (name.length > nameMaxLength) {
            $("#name_error").text("The name " + maxLengthMessage + " " + nameMaxLength + " characters !");
            return false;
        }

        if (!nameRegex.test(name)) {
            $("#name_error").text("The name can contain letters, numbers and spaces");
            return false;
        }

        $("#name_error").text("");
        return true;
    }

    function validateDescription(description) {
        if (description.length > descriptionMaxLength) {
            $("#description_error").text("The description " + maxLengthMessage + " " + descriptionMaxLength + " characters !");
            return false;
        }

        if (descriptionRegex.test(description)) {
            $("#description_error").text("The description can\"t contain < or >");
            return false;
        }

        $("#description_error").text("");
        return true;
    }

    function validateForm() {

        if($(".image").length !== 0) {
            let images = $(".form-control-file");
            images.each(function () {
                let validImg = validateImage($(this));

                if (validImg === false) {
                    return false;
                }
            });
        }

        if($(".video").length !== 0) {
            let videos = $(".video");

            videos.each(function () {
                let videoIdSplit = $(this).attr("id").split("-");
                let videoId = videoIdSplit[2];

                let validVideo = validateUrl($("#appbundle_trick_videos_" + videoId + "_url").val(), videoId);

                if (validVideo === false) {
                    return false;
                }
            });
        }

        let validName = validateName(name.val());
        let validDescription = validateDescription(description.val());

        let results = [validName, validDescription];

        if ($.inArray(false, results) !== -1) {
            return false;
        }

        return true;
    }

    function formSubmit() {
        let inputsFile = $(".form-control-file");

        if (inputsFile.length !== 0) {
            inputsFile.each(function (index) {
                let idSplit = $(this).attr("id").split("_");
                let imgPrev = $("#img-container-" + idSplit[3]);

                if ($(this).val().length === 0 && !imgPrev.hasClass("exist")) {
                    $(this).remove();
                }
            });
        }

        $("#trick_form").submit();
    }

    $("#addTrickImage").click(function (e) {
        e.preventDefault();
        uploadImage();

        return false;
    });

    $("#addTrickVideo").click(function (e) {
        e.preventDefault();
        addVideoByModal.open();
        return false;
    });

    $("#next").click(function (e) {
        e.preventDefault();

        if (canNext()) {
            pageNext();

            return false;
        }

        return false;
    });

    $("#previous").click(function (e) {
        e.preventDefault();

        if (canPrev()) {
            pagePrevious();

            return false;
        }

        return false;
    });

    $(window).resize(function () {
        if (window.innerWidth < 591) {
            $("#medias_container").hide();
            $("#media_error_container").hide();

            if ($(".media").length > 0) {
                $("#see_media_container").show();
                seeMedia = false;
            } else {
                $("#see_media_container").hide();
            }
        } else {
            $("#medias_container").show();
            $("#media_error_container").show();
            $("#see_media_container").hide();
        }

        actualPage = 1;
        refreshPagination();
    });

    $(".edit-main-trick-img").click(function (e) {
        e.preventDefault();

        if ($(".fav").length === 0) {
            $("#addTrickImage").trigger("click");
            $(this).attr("id", "edit-main-0");
            return;
        }

        let idSplit = $(this).attr("id").split("-");
        let editPrevId = "#edit-img-" + idSplit[2];
        $(editPrevId).trigger("click");

        return;
    });

    $(".delete-main-trick-img").click(function (e) {
        e.preventDefault();

        if ($(".fav").length === 0) {
            return;
        }

        let idSplit = $(this).attr("id").split("-");
        let deletePrevId = "#delete-img-" + idSplit[2];
        $(deletePrevId).trigger("click");
    });

    if(window.innerWidth < 591) {
        $("#medias_container").hide();
        $("#media_error_container").hide();

        if ($(".media").length > 0) {
            $("#see_media_container").show();
        } else {
            $("#see_media_container").hide();
        }
    }

    $("#see_media").click(function (e) {
        e.preventDefault();
        $("#see_media_container").hide();
        $("#medias_container").show();
        seeMedia = true;
        
        showPagination();
        return false;
    });

    name.on("keyup blur", function () {
        validateName($(this).val());
    });

    description.on("keyup blur", function () {
        validateDescription($(this).val());
    });
  
    window.formSubmit = formSubmit;

    $("#editTrickBtn").click(function (event) {
        event.preventDefault();
        if (validateForm()) {
            grecaptcha.reset();
            grecaptcha.execute();
        }
    });

    $("#saveBtn").click(function (event) {
        event.preventDefault();
        if (validateForm()) {
            grecaptcha.reset();
            grecaptcha.execute();
        }
    });

    $("#deleteTrickBtn").click(function (e) {
        e.preventDefault();
        deleteTrickModal.open();
    });
});