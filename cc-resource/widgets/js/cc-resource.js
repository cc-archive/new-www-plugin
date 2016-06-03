(function ($) {
    "use strict";

    function CCResource(data, options) {
        $.extend(this, this.DEFAULT_OPTIONS, options);
        this.init(data);
    };

    CCResource.prototype.DEFAULT_OPTIONS = {
    };

    CCResource.prototype.DEFAULT_DATA = {
        'url': undefined,
        'title': undefined,
        'contentHtml': undefined,
        'imageSrc': undefined,
        'imageSrcset': undefined,
        'imageSizes': undefined,
        'descriptionHtml': undefined,
        'editURL': undefined,
        'type': undefined,
        'typeName': undefined,
        'typeIconSrc': undefined,
        'typeIconSrcset': undefined,
        'typeIconSizes': undefined,
        'platformLogoSrc': undefined,
        'platformLogoSrcset': undefined,
        'platformLogoSizes': undefined,
        'platformName': undefined
    };

    CCResource.prototype.init = function(data) {
        this.data = $.extend({}, this.DEFAULT_DATA, data);
        this.resourceElem = $('<div>').addClass('resource loading');
        this.figureElem = $('<figure>').appendTo(this.resourceElem);
        this.overlayElem = $('<div>').addClass('overlay').appendTo(this.resourceElem);
        this.iconElem = undefined;

        if (data.type) {
            this.resourceElem.addClass('resource-type-'+data.type);
        }

        if (data.typeColor) {
            this.resourceElem.css('background-color', '#'+data.typeColor);
        }

        if (data.typeIconSrc) {
            this.iconElem = $('<div>').addClass('resource-icon').appendTo(this.resourceElem);
            $('<img>').attr({
                'src': data.typeIconSrc,
                'srcset': data.typeIconSrcset,
                'sizes': data.typeIconSizes,
                'alt': data.typeName
            }).appendTo(this.iconElem);
        }
    };

    CCResource.prototype.getDetailsElem = function() {
        var data = this.data,
            detailsElem = $('<div>').addClass('resource-details');

        if (data.typeName) {
            var typeWrapper = $('<div>').addClass('resource-type').appendTo(detailsElem);
            $('<span>').text(data.typeName).appendTo(typeWrapper);
        }

        var captionHtml = data.descriptionHtml || data.imageCaptionHtml || data.imageDescriptionHtml;

        if (captionHtml) {
            var captionWrapper = $('<div>').addClass('resource-caption').appendTo(detailsElem),
                captionInner = $('<p>').html(captionHtml).appendTo(captionWrapper);
            // Always open attribution links in a new window
            $('a', captionInner).attr({
                'target': '_blank',
                'rel': 'noopener'
            });
            captionInner.appendTo(captionWrapper);
        }

        if (data.platformLogoSrc) {
            $('<img>').attr({
                'class': 'resource-logo',
                'src': data.platformLogoSrc,
                'srcset': data.platformLogoSrcset,
                'sizes': data.platformLogoSizes,
                'alt': data.platformName
            }).appendTo(detailsElem);
        }

        return detailsElem;
    };

    CCResource.prototype.createElem = function(container) {
        var data = this.data;

        // We populate the figureElem when the element is first added to the
        // page. Otherwise we end up requesting way too many images at once.

        if (data.imageSrc) {
            this._preloadImage(data.imageSrc, this.resourceElem);
            this.figureElem.addClass('cc-resource-image').css({
                'background-image': 'url(\'' + data.imageSrc + '\')'
            });
        } else {
            this.figureElem.addClass('cc-resource-text').append(
                $('<p>').html(data.title)
            );
            this.resourceElem.removeClass('loading');
        }

        $('<a>').addClass('resource-click-fallthrough').attr({
            'href': data.url,
            'target': '_blank',
            'rel': 'noopener'
        }).appendTo(this.overlayElem);

        this.getDetailsElem().appendTo(this.overlayElem);

        if (data.editURL) {
            var extraElem = $('<div>').addClass('resource-extra').appendTo(this.overlayElem);
            $('<a>').addClass('resource-edit-link').attr({
                'href': data.editURL
            }).text("Edit").appendTo(extraElem);
        }

        $(container).empty().append(this.resourceElem);
    };

    CCResource.prototype._preloadImage = function(url, container) {
        var imgElem = $('<img>');
        imgElem.one('load error', function(e) {
            container.removeClass('loading');
            if (e.type == 'error') container.addClass('loading-error');
            $(this).remove();
        });
        imgElem.attr('src', url);
    };


    function CCResourceFeed(requestURL, options) {
        $.extend(this, this.DEFAULT_OPTIONS, options);
        this.init(requestURL);
    };

    CCResourceFeed.prototype.DEFAULT_OPTIONS = {
        'batchSize': 60,
        /* Always limit the number of resources to load */
        'defaultConnectionLimit': 250,
        'meteredConnectionLimit': 30,
        /* It is typically safe to assume that these connection types are metered */
        'meteredConnectionTypes': ['bluetooth', 'cellular', 'wimax'],
        'onResourcesLoaded': function() {}
    };

    CCResourceFeed.prototype.init = function(requestURL) {
        this.requestURL = requestURL;
        this.initialId = 0;
        this.resourcesTotal = undefined;
        this.resourcesRemaining = undefined;
        this.incomingData = [];
        this.nextRequestStart = 0;
        this.loading = undefined;
    };

    CCResourceFeed.prototype.next = function() {
        /* TODO: Instead, we should check hasNext and call loadMoreFromServer
         *       automatically if we aren't at the end */
        var data = this.incomingData.shift(),
            resource = new CCResource(data);
        return resource;
    };

    CCResourceFeed.prototype.hasNext = function() {
        return this.incomingData.length > 0;
    };

    CCResourceFeed.prototype.loadMoreFromServer = function() {
        var _this = this;

        if (this.loading && this.loading.status === undefined) return;

        var requestEnd = this.getMaximum() - this.nextRequestStart,
            requestCount = (requestEnd > 0) ? Math.min(this.batchSize, requestEnd) : 0;

        if (requestCount > 0) {
            this.loading = $.ajax({
                'url': this.requestURL,
                'type': 'get',
                'dataType': 'json',
                'data': {
                    'action': 'get_resources',
                    'first': this.initialId,
                    'start': this.nextRequestStart,
                    'count': requestCount
                }
            }).done(function(data, textStatus, jqXHR) {
                _this.addResourcesFromData(data);
            });
        }
    };

    CCResourceFeed.prototype.hasMoreFromServer = function() {
        return this.resourcesRemaining === undefined || this.resourcesRemaining > 0;
    };

    CCResourceFeed.prototype.getMaximum = function() {
        var maximum = this.getConnectionLimit();

        if (this.resourcesTotal !== undefined && maximum !== undefined) {
            maximum = Math.min(maximum, this.resourcesTotal);
        } else {
            maximum = this.resourcesTotal;
        }

        return maximum;
    };

    CCResourceFeed.prototype.getConnectionLimit = function() {
        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection,
            connectionType = (connection) ? connection.type : undefined,
            isMetered = this.meteredConnectionTypes.indexOf(connectionType) >= 0;

        return (isMetered) ? this.meteredConnectionLimit : this.defaultConnectionLimit;
    };

    CCResourceFeed.prototype.addResourcesFromData = function(data) {
        data = data || {};

        this.resourcesTotal = data['total'];
        this.resourcesRemaining = data['remaining'];

        var newResources = data['resources'] || [];

        if (newResources) {
            if (this.initialId == 0) {
                var firstResource = newResources[0] || {};
                this.initialId = firstResource.id;
            }
            Array.prototype.push.apply(this.incomingData, newResources);
            this.nextRequestStart += newResources.length;
            this.onResourcesLoaded();
        }
    };


    function CCResourceGrid(container, options) {
        $.extend(this, this.DEFAULT_OPTIONS, options);
        this.init(container);
    };

    CCResourceGrid.prototype.DEFAULT_OPTIONS = {
        'maximum': undefined,
        'maxExtraRows': 3
    };

    CCResourceGrid.prototype.DEFAULT_ADD_TILES_OPTIONS = {
        'initial': false
    };

    CCResourceGrid.prototype.init = function(container) {
        this.container = $(container);
        this.tileWidth = undefined;
        this.tileHeight = undefined;
        this.rowSize = undefined;
        // List of resource tiles with class "empty", duplicated here to avoid
        // hammering the browser with DOM lookups
        this.emptyTiles = [];
        this.tilesCount = 0;

        this.container.on('mouseenter click', '.resource', function(e) {
            var isActive = $(this).hasClass('active');
            if (!isActive) {
                $('.resource.active', container).not(this).removeClass('active');
                $(this).addClass('active');
                e.preventDefault();
            }
        });

        this.container.on('mouseleave', '.resource', function(e) {
            $(this).removeClass('active');
        });

        // Disable mouse hover events for touch devices. This is an ugly hack
        // to avoid the double tap problem.
        this.container.one('touchstart', '.resource', function(e) {
            $(container).off('mouseenter');
        });
    };

    CCResourceGrid.prototype.updateDimensions = function() {
        // Cache element dimensions that only change on resize
        var aTile = this.container.children('.resource-tile').first();
        if (aTile.length > 0) {
            var listWidth = this.container.outerWidth();
            this.tileWidth = Math.ceil(aTile.outerWidth()),
            this.tileHeight = Math.ceil(aTile.outerHeight());
        } else {
            this.tileWidth = 0;
            this.tileHeight = 0;
        }
        this.rowSize = this.tileWidth > 0 ? Math.ceil(listWidth / this.tileWidth) : undefined;
        this.maxExtraRows = Math.ceil($(window).height() / this.tileHeight);
    };

    CCResourceGrid.prototype.setInitialDimensions = function(addTilesOptions) {
        this.addTiles(1, addTilesOptions);
        this.updateDimensions();
    };

    CCResourceGrid.prototype.updateOnScreen = function() {
        // Loops through resource tiles and marks them if they are offscreen.
        // This may be a good place to proactively unload images if we need to.

        var scrollTop = $(window).scrollTop(),
            scrollBottom = scrollTop + $(window).height();

        var footerElem = $('.site-footer.sticky');
        if (footerElem.hasClass('detached') && !footerElem.hasClass('offscreen')) {
            scrollBottom -= footerElem.height();
        }

        $('.resource-tile', this.container).each(function(index, resourceTile) {
            var tileTop = $(resourceTile).offset().top,
                tileBottom = tileTop + $(resourceTile).height();
            if (scrollTop < tileBottom && scrollBottom > tileTop) {
                // It is tempting to remove offscreen resources from the DOM,
                // but modern browsers do the important bits automatically.
                $(resourceTile).removeClass('offscreen offscreen-above offscreen-below never-shown').addClass('onscreen')
            } else if (scrollTop > tileBottom) {
                $(resourceTile).removeClass('onscreen offscreen-below').addClass('offscreen offscreen-above');
            } else {
                $(resourceTile).removeClass('onscreen offscreen-above').addClass('offscreen offscreen-below');
            }
        });
    };

    CCResourceGrid.prototype.getRemaining = function() {
        if (this.maximum !== undefined) {
            return this.maximum - this.tilesCount;
        } else {
            return undefined;
        }
    };

    CCResourceGrid.prototype.addTiles = function(count, addTilesOptions) {
        var remainingTiles = this.getRemaining(),
            options = $.extend({}, this.DEFAULT_ADD_TILES_OPTIONS, addTilesOptions);

        if (remainingTiles !== undefined) {
            count = Math.min(count, remainingTiles);
        }

        for (var i = 0; i < count; i++) {
            var resourceTile = $('<div>').addClass('resource-tile empty');
            if (!options.initial) resourceTile.addClass('never-shown');
            this.emptyTiles.push(resourceTile);
            resourceTile.appendTo(this.container);
        }

        this.tilesCount += count;
        return count;
    };

    CCResourceGrid.prototype.addRows = function(rows, addTilesOptions) {
        if (this.rowSize === undefined) this.setInitialDimensions(addTilesOptions);

        // Add enough resource tiles to fill the given number of rows.
        // We calculate a remainder to keep everything square.
        var tilesNeeded = rows * this.rowSize,
            remainder = (this.tilesCount + tilesNeeded) % this.rowSize;
        return this.addTiles(tilesNeeded + remainder, addTilesOptions);
    };

    CCResourceGrid.prototype.addRowsForSpace = function(scrollBottom, velocity, addTilesOptions) {
        if (this.tileHeight === undefined) this.setInitialDimensions(addTilesOptions);

        var padding = this.tileHeight || 0,
            listBottom = this.container.offset().top + this.container.outerHeight(),
            triggerEdge = listBottom - padding,
            distanceFromEdge = scrollBottom - triggerEdge,
            rowsNeeded = (padding > 0) ? Math.ceil(distanceFromEdge / padding) : 0;

        if (rowsNeeded > 0) {
            var extraRows = (velocity && padding > 0) ? Math.ceil(velocity / padding) : 0;
            // Load as many rows as we need, and a bit extra
            var newRows = Math.min(this.maxExtraRows, rowsNeeded + extraRows);
            return this.addRows(newRows, addTilesOptions);
        } else {
            return 0;
        }
    };

    CCResourceGrid.prototype.next = function() {
        var resourceTile = this.emptyTiles.shift();
        return resourceTile;
    };

    CCResourceGrid.prototype.fillTiles = function(resources) {
        // Loop through empty resource tiles and add loaded resources to them.
        while (this.hasNext() && resources.hasNext()) {
            var resourceTile = this.next(),
                resource = resources.next();
            resource.createElem(resourceTile);
            resourceTile.removeClass('empty');
        }

        // Return true if all tiles were filled; false if we ran out of resources.
        return resources.hasNext() || !this.hasNext();
    }

    CCResourceGrid.prototype.hasNext = function() {
        return this.emptyTiles.length > 0;
    };


    $(document).ready(function() {
        var resourceFeed = undefined,
            resourceGrid = undefined;

        var fillEmptyResourceTiles = function() {
            var needsMoreResources = !resourceGrid.fillTiles(resourceFeed);
            if (needsMoreResources && resourceFeed.hasMoreFromServer()) {
                resourceFeed.loadMoreFromServer();
            }
        };

        var onResourcesLoaded = function() {
            resourceGrid.maximum = resourceFeed.getMaximum();
            fillEmptyResourceTiles();
        };

        var onResizeCb = function(e) {
            resourceGrid.updateDimensions();
            resourceGrid.updateOnScreen();
        };

        var onScrollCb = function(e, params) {
            resourceGrid.updateOnScreen();

            var newTilesCount = 0,
                velocity = params['velocity'],
                bottom = params['bottom'];

            if (velocity === undefined) {
                newTilesCount = resourceGrid.addRowsForSpace(bottom, undefined);
            } else if (velocity > 0) {
                newTilesCount = resourceGrid.addRowsForSpace(bottom + velocity, velocity);
            }

            if (newTilesCount > 0) {
                fillEmptyResourceTiles();
            }
        };

        resourceFeed = new CCResourceFeed(CC_RESOURCE.ajaxurl, {
            'onResourcesLoaded': onResourcesLoaded
        });
        resourceGrid = new CCResourceGrid('.resource-list');

        resourceFeed.addResourcesFromData(CC_RESOURCE.initial);
        var initialTilesCount = resourceGrid.addRows(2, {
            'initial': true
        });
        if (initialTilesCount > 0) {
            fillEmptyResourceTiles(initialTilesCount);
        }

        $(window).on('resize', onResizeCb);
        $(document).on('cc-scroll', onScrollCb);
    });
})(jQuery);
