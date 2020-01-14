// Create a new module
var app = angular.module('mainPage', ['ui.bootstrap', 'collectionsPage']);

app.factory('slideUtils', function($http) {
    return {
        loadSlides: function(s) {
            $http({
                url: '/php/loadSlides.php',
                method: "GET"
             }).then(function(response) {
                console.log(response);
                s.slides = response.data;
            });
        }
    }
});

app.controller('mainPageController', function($http, $scope, slideUtils) {
    $scope.slides = null;
    $scope.sleep = 5000;
    $scope.loadSlides = slideUtils.loadSlides($scope);
});

app.controller('bannerPageController', function($http, $scope, $location, slideUtils, adminUtils) {
    $scope.loadSlides = slideUtils.loadSlides($scope);
    $scope.status = $location.search().upload;
    $scope.message = $location.search().message;

    $scope.previewData = [];

    $scope.showImage = false;
    $scope.slideID = null;
    $scope.slideName = null;
    $scope.caption = null;
    $scope.imageName = null;
    $scope.link = null;
    $scope.slideIndex = null;
    $scope.selected = null;

    $scope.setSlide = function() {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            for (var key in $scope.slides) {
                var slide = $scope.slides[key];
                if ($scope.selected.SlideIndex == slide.SlideIndex) {
                    $scope.slideID = slide.SlideID;
                    $scope.slideName = slide.Name;
                    $scope.caption = slide.Caption;
                    $scope.image = slide.Image;
                    $scope.link = slide.Link;
                    $scope.slideIndex = slide.SlideIndex;

                    // Show product image
                    $scope.showImage = true;
                    $scope.previewData['data'] = "/images/" + $scope.image;
                }
            }
        }
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            $scope.selected = $scope.slides[0];
            for (var key in $scope.slides) {
                var slide = $scope.slides[key];
                if ($scope.slideID == slide.SlideID) {
                    $scope.selected = slide;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['slides'], function () {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            $scope.slideID = $scope.slides[0]["SlideID"];
            $scope.slideIndex = $scope.slides[0]["SlideIndex"];
            $scope.setSelected();
            $scope.setSlide();
        }
    });

    $scope.setImage = function(fileData) {
        if (!isEmpty(fileData)) {
            name = fileData["name"];
            $scope.previewData = fileData;
            // Utilizing non angular event call so must inform angular that scope variables are changing.
            $scope.$apply(function() {
                if ($scope.imageName == null) {
                    $scope.imageName = name;
                }
                if ($scope.slideName == null) {
                    $scope.slideName = name.substring(0, name.lastIndexOf('.')).replace(/_/g, " ");
                }
                $scope.showImage = true;
            });
        }
    }
    $scope.removeInput = function() {
        // Unload file
        $("input[type='file']").val("");
        $scope.previewData = [];
        $scope.showImage = false;
    }
    $scope.readInput = function(input) {
        adminUtils.readInput(input, {
            success: $scope.setImage,
            fail: function() {$scope.$apply($scope.removeInput())}
        });
    }
});