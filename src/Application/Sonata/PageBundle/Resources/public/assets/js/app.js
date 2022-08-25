!(function () {
    var t = {
            266: function (t, e, i) {
                var o = i(311);
                function s(t) {
                    void 0 !== window.Admin && window.Admin.shared_setup(t);
                }
                function n(t, e) {
                    const i = e || {};
                    (this.pageId = t),
                        (this.$container = o(".page-composer")),
                        (this.$dynamicArea = o(".page-composer__dyn-content")),
                        (this.$pagePreview = o(".page-composer__page-preview")),
                        (this.$containerPreviews = this.$pagePreview.find(".page-composer__page-preview__container")),
                        (this.routes = o.extend({}, i.routes || {})),
                        (this.translations = o.extend({}, i.translations || {})),
                        (this.csrfTokens = o.extend({}, i.csrfTokens || {}));
                }
                (n.prototype = {
                    init() {
                        this.bindPagePreviewHandlers(), this.bindOrphansHandlers();
                        const t = this,
                            e = o(this);
                        e.on("containerclick", (e) => {
                            t.loadContainer(e.$container);
                        }),
                            e.on("containerloaded", this.handleContainerLoaded),
                            e.on("blockcreated", this.handleBlockCreated),
                            e.on("blockremoved", this.handleBlockRemoved),
                            e.on("blockcreateformloaded", this.handleBlockCreateFormLoaded),
                            e.on("blockpositionsupdate", this.handleBlockPositionsUpdate),
                            e.on("blockeditformloaded", this.handleBlockEditFormLoaded),
                            e.on("blockparentswitched", this.handleBlockParentSwitched);
                    },
                    translate(t) {
                        return this.translations[t] ? this.translations[t] : t;
                    },
                    getRouteUrl(t, e) {
                        if (!this.routes[t]) throw new Error('Route "'.concat(t, '" does not exist'));
                        let i = this.routes[t];
                        for (const t in e) i = i.replace(new RegExp(t), e[t]);
                        return i;
                    },
                    isFormControlTypeByName(t, e) {
                        if (void 0 !== t) {
                            let i = t.length;
                            const o = "[".concat(e, "]"),
                                s = t.lastIndexOf(o);
                            return (i -= o.length), -1 !== s && s === i;
                        }
                        return !1;
                    },
                    handleBlockCreated(t) {
                        const e = this;
                        o.ajax({
                            url: this.getRouteUrl("block_preview", { BLOCK_ID: t.blockId }),
                            type: "GET",
                            success(i) {
                                const s = o(i);
                                t.$childBlock.replaceWith(s), e.controlChildBlock(s);
                                const n = e.getContainerChildCountFromList(t.parentId);
                                null !== n && e.updateChildCount(t.parentId, n);
                            },
                            error() {
                                e.containerNotification("composer_preview_error", "error", !0);
                            },
                        });
                    },
                    handleBlockRemoved(t) {
                        const e = this.getContainerChildCountFromList(t.parentId);
                        null !== e && this.updateChildCount(t.parentId, e);
                    },
                    containerNotification(t, e, i) {
                        const s = this.$dynamicArea.find(".page-composer__container__view__notice");
                        if (1 === s.length)
                            if ((this.containerNotificationTimer && clearTimeout(this.containerNotificationTimer), s.removeClass("persist success error"), e && s.addClass(e), s.text(this.translate(t)), s.show(), !0 !== i))
                                this.containerNotificationTimer = setTimeout(() => {
                                    s.hide().empty();
                                }, 2e3);
                            else {
                                const t = o('<span class="close-notice">x</span>');
                                t.on("click", () => {
                                    s.hide().empty();
                                }),
                                    s.addClass("persist"),
                                    s.append(t);
                            }
                    },
                    handleBlockPositionsUpdate(t) {
                        const e = this;
                        this.containerNotification("composer_update_saving"),
                            o.ajax({
                                url: this.getRouteUrl("save_blocks_positions"),
                                type: "POST",
                                data: { disposition: t.disposition },
                                success(t) {
                                    t.result && "ok" === t.result && e.containerNotification("composer_update_saved", "success");
                                },
                                error() {
                                    e.containerNotification("composer_update_error", "error", !0);
                                },
                            });
                    },
                    handleBlockParentSwitched(t) {
                        const e = o(".block-preview-".concat(t.previousParentId)).find(".child-count"),
                            i = parseInt(e.text().trim(), 10),
                            s = o(".block-preview-".concat(t.newParentId)).find(".child-count"),
                            n = parseInt(s.text().trim(), 10);
                        this.updateChildCount(t.previousParentId, i - 1), this.updateChildCount(t.newParentId, n + 1);
                    },
                    getContainerChildCountFromList(t) {
                        const e = this.$dynamicArea.find(".block-view-".concat(t));
                        if (0 === e.length) return null;
                        const i = e.find(".page-composer__container__child");
                        let s = 0;
                        return (
                            i.each(function () {
                                void 0 !== o(this).attr("data-block-id") && (s += 1);
                            }),
                            s
                        );
                    },
                    updateChildCount(t, e) {
                        const i = o(".block-preview-".concat(t)),
                            s = o(".block-view-".concat(t));
                        i.length > 0 && i.find(".child-count").text(e), s.length > 0 && s.find(".page-composer__container__child-count span").text(e);
                    },
                    handleBlockCreateFormLoaded(t) {
                        const e = this,
                            i = this.$dynamicArea.find(".page-composer__container__children"),
                            n = this.$dynamicArea.find(".page-composer__container__main-edition-area");
                        let r = null,
                            a = null;
                        t.container
                            ? ((r = t.container), (a = r.find(".page-composer__container__child__content")), a.html(t.response))
                            : ((r = o(
                                  [
                                      '<li class="page-composer__container__child">',
                                      '<a class="page-composer__container__child__edit">',
                                      '<h4 class="page-composer__container__child__name">',
                                      '<input type="text" class="page-composer__container__child__name__input">',
                                      "</h4>",
                                      "</a>",
                                      '<div class="page-composer__container__child__right">',
                                      '<span class="badge">'.concat(t.blockTypeLabel, "</span>"),
                                      "</div>",
                                      '<div class="page-composer__container__child__content">',
                                      "</div>",
                                      "</li>",
                                  ].join("")
                              )),
                              (a = r.find(".page-composer__container__child__content")),
                              a.append(t.response),
                              i.append(r),
                              a.show());
                        const l = r.find("form"),
                            c = l.attr("action"),
                            p = l.attr("method"),
                            h = l.find("input, select, textarea"),
                            d = l.find(".form-actions"),
                            u = this.$dynamicArea.find(".page-composer__container__child__name");
                        let f, m, g;
                        s(l),
                            o(document).scrollTo(r, 200),
                            n.show(),
                            h.each(function () {
                                const s = o(this),
                                    n = s.attr("name");
                                e.isFormControlTypeByName(n, "name")
                                    ? ((f = s),
                                      u.find(".page-composer__container__child__name__input").on("propertychange keyup input paste", function () {
                                          f.val(o(this).val());
                                      }))
                                    : e.isFormControlTypeByName(n, "parent")
                                    ? ((m = s), m.val(t.containerId), m.parent().parent().hide())
                                    : e.isFormControlTypeByName(n, "position") && ((g = s), g.val(i.find("> *").length - 1), g.closest(".form-group").hide());
                            }),
                            d.each(function () {
                                const t = o(this),
                                    i = o('<span class="btn btn-warning">'.concat(e.translate("cancel"), "</span>"));
                                i.on("click", (t) => {
                                    t.preventDefault(), r.remove(), o(document).scrollTo(e.$dynamicArea, 200);
                                }),
                                    t.append(i);
                            }),
                            l.on("submit", (i) => {
                                i.preventDefault();
                                let n = f.val();
                                return (
                                    "" === n && (n = t.blockType),
                                    o.ajax({
                                        url: c,
                                        data: l.serialize(),
                                        type: p,
                                        headers: { Accept: "application/json" },
                                        success(i) {
                                            if (i.result && "ok" === i.result && i.objectId) {
                                                const s = o.Event("blockcreated");
                                                (s.$childBlock = r), (s.parentId = t.containerId), (s.blockId = i.objectId), (s.blockName = n), (s.blockType = t.blockType), o(e).trigger(s);
                                            } else {
                                                const n = o.Event("blockcreateformloaded");
                                                (n.response = i), (n.containerId = t.containerId), (n.blockType = t.blockType), (n.container = r), o(e).trigger(n), s(a);
                                            }
                                        },
                                    }),
                                    !1
                                );
                            });
                    },
                    toggleChildBlock(t) {
                        const e = "page-composer__container__child--expanded",
                            i = this.$dynamicArea.find(".page-composer__container__child"),
                            o = t.find(".page-composer__container__child__name"),
                            s = o.find(".page-composer__container__child__name__input");
                        t.hasClass(e) ? (t.removeClass(e), o.has(".page-composer__container__child__name__input") && o.html(s.val())) : (i.not(t).removeClass(e), t.addClass(e));
                    },
                    handleBlockEditFormLoaded(t) {
                        const e = this,
                            i = t.$block.find(".page-composer__container__child__edit h4"),
                            n = t.$block.find(".page-composer__container__child__content"),
                            r = t.$block.find(".page-composer__container__child__loader"),
                            a = n.find("form"),
                            l = a.attr("action"),
                            c = a.attr("method"),
                            p = t.$block.find(".page-composer__container__child__edit small").text().trim();
                        let h, d;
                        a.find("input").each(function () {
                            const t = o(this),
                                s = t.attr("name");
                            if (e.isFormControlTypeByName(s, "name")) {
                                (h = t), i.html('<input type="text" class="page-composer__container__child__name__input" value="'.concat(i.text().trim(), '">'));
                                const e = i.find("input");
                                e.bind("propertychange keyup input paste", () => {
                                    h.val(e.val());
                                }),
                                    e.on("click", (t) => {
                                        t.stopPropagation(), t.preventDefault();
                                    });
                            } else e.isFormControlTypeByName(s, "position") && ((d = t), d.closest(".form-group").hide());
                        }),
                            a.on(
                                "submit",
                                (d) => (
                                    d.preventDefault(),
                                    r.show(),
                                    o.ajax({
                                        url: l,
                                        data: a.serialize(),
                                        type: c,
                                        headers: { Accept: "application/json" },
                                        success(a) {
                                            if ((r.hide(), a.result && "ok" === a.result)) void 0 !== h && i.text("" !== h.val() ? h.val() : p), t.$block.removeClass("page-composer__container__child--expanded"), n.empty();
                                            else {
                                                n.html(a);
                                                const i = o.Event("blockeditformloaded");
                                                (i.$block = t.$block), o(e).trigger(i), s(n);
                                            }
                                        },
                                    }),
                                    !1
                                )
                            );
                    },
                    controlChildBlock(t) {
                        const e = this,
                            i = t.find(".page-composer__container__child__content"),
                            n = t.find(".page-composer__container__child__loader"),
                            r = t.find(".page-composer__container__child__edit"),
                            a = r.attr("href"),
                            l = t.find(".page-composer__container__child__remove").find("a"),
                            c = t.find(".page-composer__container__child__switch-enabled"),
                            p = c.attr("data-label-enable"),
                            h = c.attr("data-label-disable"),
                            d = c.find("a"),
                            u = d.find("i"),
                            f = t.find(".page-composer__container__child__enabled"),
                            m = f.find("small"),
                            g = f.find("i"),
                            _ = d.attr("href");
                        let v = parseInt(t.attr("data-block-enabled"), 2);
                        r.click((r) => {
                            r.preventDefault(),
                                i.find("form").length > 0
                                    ? e.toggleChildBlock(t)
                                    : (n.show(),
                                      o.ajax({
                                          url: a,
                                          success(r) {
                                              i.html(r);
                                              const a = o.Event("blockeditformloaded");
                                              (a.$block = t), o(e).trigger(a), s(i), n.hide(), e.toggleChildBlock(t);
                                          },
                                      }));
                        }),
                            d.on("click", (i) => {
                                i.preventDefault(),
                                    o.ajax({
                                        url: _,
                                        type: "POST",
                                        data: { _sonata_csrf_token: e.csrfTokens.switchEnabled, value: !v },
                                        success() {
                                            if (
                                                (t.attr("data-block-enabled", v ? "0" : "1"),
                                                (v = !v),
                                                d.toggleClass("bg-yellow bg-green"),
                                                u.toggleClass("fa-toggle-off fa-toggle-on"),
                                                v ? d.html(h) : d.html(p),
                                                m.toggleClass("bg-yellow bg-green"),
                                                g.toggleClass("fa-times fa-check"),
                                                t.has("form"))
                                            ) {
                                                t.find("form")
                                                    .find("input")
                                                    .each(function () {
                                                        const t = o(this),
                                                            i = t.attr("name");
                                                        e.isFormControlTypeByName(i, "enabled") && t.val(parseInt(!v, 10));
                                                    });
                                            }
                                        },
                                        error() {
                                            e.containerNotification("composer_status_error", "error", !0);
                                        },
                                    });
                            }),
                            l.on("click", (i) => {
                                i.preventDefault(), e.confirmRemoveContainer(t);
                            });
                    },
                    confirmRemoveContainer(t) {
                        const e = this,
                            i = t.find(".page-composer__container__child__remove").find("a");
                        let s = t.find(".page-composer__container__child__remove__dialog");
                        const n = i.attr("href"),
                            r = parseInt(t.attr("data-parent-block-id"), 10);
                        0 === s.length &&
                            ((s = o(
                                [
                                    '<div class="modal fade page-composer__container__child__remove__dialog" tabindex="-1" role="dialog">',
                                    '<div class="modal-dialog" role="document">',
                                    '<div class="modal-content">',
                                    '<div class="modal-header">',
                                    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
                                    '<h4 class="modal-title">'.concat(this.translate("composer_remove_confirm"), "</h4>"),
                                    "</div>",
                                    '<div class="modal-body">',
                                    "</div>",
                                    '<div class="modal-footer">',
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">'.concat(this.translate("cancel"), "</button>"),
                                    '<button type="button" class="btn btn-primary">'.concat(this.translate("yes"), "</button>"),
                                    "</div>",
                                    "</div>",
                                    "</div>",
                                    "</div>",
                                ].join("")
                            )),
                            t.append(s));
                        s.find(".btn-primary").on("click", () => {
                            o.ajax({
                                url: n,
                                type: "POST",
                                data: { _method: "DELETE", _sonata_csrf_token: e.csrfTokens.remove },
                                success(i) {
                                    if (i.result && "ok" === i.result) {
                                        t.remove();
                                        const i = o.Event("blockremoved");
                                        (i.parentId = r), o(e).trigger(i);
                                    }
                                },
                            }),
                                s.modal("hide"),
                                0 !== o(".modal-backdrop").length && o(".modal-backdrop").hide();
                        }),
                            s.modal("show");
                    },
                    handleContainerLoaded(t) {
                        const e = this,
                            i = this.$dynamicArea.find(".page-composer__container__children"),
                            n = this.$dynamicArea.find(".page-composer__container__child"),
                            r = this.$dynamicArea.find(".page-composer__block-type-selector"),
                            a = r.find(".page-composer__block-type-selector__loader"),
                            l = r.find("select"),
                            c = r.find(".page-composer__block-type-selector__confirm"),
                            p = c.attr("href");
                        s(this.$dynamicArea),
                            c.on("click", (i) => {
                                i.preventDefault(), a.css("display", "inline-block");
                                const s = l.val(),
                                    n = l.find("option:selected").text().trim();
                                o.ajax({
                                    url: p,
                                    data: { type: s },
                                    success(i) {
                                        a.hide(), o(e).trigger(o.Event("blockcreateformloaded", { response: i, containerId: t.containerId, blockType: s, blockTypeLabel: n }));
                                    },
                                });
                            }),
                            i.sortable({
                                revert: !0,
                                cursor: "move",
                                revertDuration: 200,
                                delay: 200,
                                helper(t, e) {
                                    const i = o(e),
                                        s = i.find(".page-composer__container__child__edit h4").text().trim();
                                    return i.removeClass("page-composer__container__child--expanded"), o('<div class="page-composer__container__child__helper"><h4>'.concat(s, "</h4></div>"));
                                },
                                update() {
                                    const t = [];
                                    if (
                                        (i.find(".page-composer__container__child").each(function (i) {
                                            const s = o(this),
                                                n = s.attr("data-parent-block-id"),
                                                r = s.attr("data-block-id");
                                            void 0 !== r && t.push({ id: parseInt(r, 10), position: i, parent_id: parseInt(n, 10), page_id: e.pageId });
                                        }),
                                        t.length > 0)
                                    ) {
                                        const i = o.Event("blockpositionsupdate");
                                        (i.disposition = t), o(e).trigger(i);
                                    }
                                },
                            }),
                            n.each(function () {
                                e.controlChildBlock(o(this));
                            });
                    },
                    bindPagePreviewHandlers() {
                        const t = this;
                        this.$containerPreviews
                            .each(function () {
                                const e = o(this);
                                e.on("click", (i) => {
                                    i.preventDefault();
                                    const s = o.Event("containerclick");
                                    (s.$container = e), o(t).trigger(s);
                                });
                            })
                            .droppable({
                                hoverClass: "hover",
                                tolerance: "pointer",
                                revert: !0,
                                connectToSortable: ".page-composer__container__children",
                                accept(t) {
                                    let e = o(this).attr("data-block-allowlist");
                                    if ("" === e) return !0;
                                    e = e.split(",");
                                    const i = o(t).attr("data-block-type");
                                    return -1 !== e.indexOf(i);
                                },
                                drop(e, i) {
                                    let s = i.draggable.attr("data-block-id");
                                    if (void 0 !== s) {
                                        i.helper.remove();
                                        const e = o(this),
                                            n = parseInt(i.draggable.attr("data-parent-block-id"), 10),
                                            r = parseInt(e.attr("data-block-id"), 10);
                                        (s = parseInt(s, 10)),
                                            n !== r &&
                                                (e.addClass("dropped"),
                                                e.on("webkitAnimationEnd oanimationend msAnimationEnd animationend", () => {
                                                    e.removeClass("dropped");
                                                }),
                                                o.ajax({
                                                    url: t.getRouteUrl("block_switch_parent"),
                                                    data: { block_id: s, parent_id: r },
                                                    success(e) {
                                                        if (e.result && "ok" === e.result) {
                                                            i.draggable.remove();
                                                            const e = o.Event("blockparentswitched");
                                                            (e.previousParentId = n), (e.newParentId = r), (e.blockId = s), o(t).trigger(e);
                                                        }
                                                    },
                                                }));
                                    }
                                },
                            }),
                            this.$containerPreviews.length > 0 && this.loadContainer(this.$containerPreviews.eq(0));
                    },
                    bindOrphansHandlers() {
                        const t = this;
                        this.$container.find(".page-composer__orphan-container").each(function () {
                            const e = o(this);
                            e.on("click", (i) => {
                                i.preventDefault();
                                const s = o.Event("containerclick");
                                (s.$container = e), o(t).trigger(s);
                            });
                        });
                    },
                    loadContainer(t) {
                        const e = t.attr("href"),
                            i = t.attr("data-block-id"),
                            s = this;
                        this.$dynamicArea.empty(),
                            this.$containerPreviews.removeClass("active"),
                            this.$container.find(".page-composer__orphan-container").removeClass("active"),
                            t.addClass("active"),
                            o.ajax({
                                url: e,
                                success(t) {
                                    s.$dynamicArea.html(t), o(document).scrollTo(s.$dynamicArea, 200, { offset: { top: -100 } });
                                    const e = o.Event("containerloaded");
                                    (e.containerId = i), o(s).trigger(e);
                                },
                            });
                    },
                }),
                    o(() => {
                        o("[data-page-composer]").each(function () {
                            const t = o(this).data("page-composer");
                            new n(t.pageId, t).init();
                        });
                    });
            },
            916: function (t, e, i) {
                var o = i(311);
                const s = "treeView",
                    n = { togglersAttribute: "[data-treeview-toggler]", toggledState: "is-toggled" };
                function r(t, e) {
                    (this.element = t), (this.options = o.extend({}, n, e)), (this.defaults = n), (this.name = s);
                }
                (r.prototype = {
                    init() {
                        this.setElements(), this.setEvents();
                    },
                    setElements() {
                        (this.$element = o(this.element)), (this.$togglers = this.$element.find(this.options.togglersAttribute));
                    },
                    setEvents() {
                        this.$togglers.on("click", this.toggle.bind(this));
                    },
                    toggle(t) {
                        const e = o(t.currentTarget).parent();
                        e.toggleClass(this.options.toggledState), e.next("ul").slideToggle();
                    },
                }),
                    (o.fn.treeView = function (t) {
                        return this.each(function () {
                            if (!o.data(this, "plugin_".concat(s))) {
                                const e = new r(this, t);
                                e.init(), o.data(this, "plugin_".concat(s), e);
                            }
                        });
                    });
            },
            400: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return t.extend(t.expr.pseudos, {
                                        data: t.expr.createPseudo
                                            ? t.expr.createPseudo(function (e) {
                                                  return function (i) {
                                                      return !!t.data(i, e);
                                                  };
                                              })
                                            : function (e, i, o) {
                                                  return !!t.data(e, o[3]);
                                              },
                                    });
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            870: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (t.ui.ie = !!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase()));
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            624: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        (o = function (t) {
                            return (t.ui.plugin = {
                                add: function (e, i, o) {
                                    var s,
                                        n = t.ui[e].prototype;
                                    for (s in o) (n.plugins[s] = n.plugins[s] || []), n.plugins[s].push([i, o[s]]);
                                },
                                call: function (t, e, i, o) {
                                    var s,
                                        n = t.plugins[e];
                                    if (n && (o || (t.element[0].parentNode && 11 !== t.element[0].parentNode.nodeType))) for (s = 0; s < n.length; s++) t.options[n[s][0]] && n[s][1].apply(t.element, i);
                                },
                            });
                        }),
                        void 0 === (n = "function" == typeof o ? o.apply(e, s) : o) || (t.exports = n);
                })();
            },
            575: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (t.ui.safeActiveElement = function (t) {
                                        var e;
                                        try {
                                            e = t.activeElement;
                                        } catch (i) {
                                            e = t.body;
                                        }
                                        return e || (e = t.body), e.nodeName || (e = t.body), e;
                                    });
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            192: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (t.ui.safeBlur = function (e) {
                                        e && "body" !== e.nodeName.toLowerCase() && t(e).trigger("blur");
                                    });
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            464: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (t.fn.scrollParent = function (e) {
                                        var i = this.css("position"),
                                            o = "absolute" === i,
                                            s = e ? /(auto|scroll|hidden)/ : /(auto|scroll)/,
                                            n = this.parents()
                                                .filter(function () {
                                                    var e = t(this);
                                                    return (!o || "static" !== e.css("position")) && s.test(e.css("overflow") + e.css("overflow-y") + e.css("overflow-x"));
                                                })
                                                .eq(0);
                                        return "fixed" !== i && n.length ? n : t(this[0].ownerDocument || document);
                                    });
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            592: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (t.ui = t.ui || {}), (t.ui.version = "1.13.2");
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            891: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(592)]),
                        (o = function (t) {
                            var e = 0,
                                i = Array.prototype.hasOwnProperty,
                                o = Array.prototype.slice;
                            return (
                                (t.cleanData = (function (e) {
                                    return function (i) {
                                        var o, s, n;
                                        for (n = 0; null != (s = i[n]); n++) (o = t._data(s, "events")) && o.remove && t(s).triggerHandler("remove");
                                        e(i);
                                    };
                                })(t.cleanData)),
                                (t.widget = function (e, i, o) {
                                    var s,
                                        n,
                                        r,
                                        a = {},
                                        l = e.split(".")[0],
                                        c = l + "-" + (e = e.split(".")[1]);
                                    return (
                                        o || ((o = i), (i = t.Widget)),
                                        Array.isArray(o) && (o = t.extend.apply(null, [{}].concat(o))),
                                        (t.expr.pseudos[c.toLowerCase()] = function (e) {
                                            return !!t.data(e, c);
                                        }),
                                        (t[l] = t[l] || {}),
                                        (s = t[l][e]),
                                        (n = t[l][e] = function (t, e) {
                                            if (!this || !this._createWidget) return new n(t, e);
                                            arguments.length && this._createWidget(t, e);
                                        }),
                                        t.extend(n, s, { version: o.version, _proto: t.extend({}, o), _childConstructors: [] }),
                                        ((r = new i()).options = t.widget.extend({}, r.options)),
                                        t.each(o, function (t, e) {
                                            a[t] =
                                                "function" == typeof e
                                                    ? (function () {
                                                          function o() {
                                                              return i.prototype[t].apply(this, arguments);
                                                          }
                                                          function s(e) {
                                                              return i.prototype[t].apply(this, e);
                                                          }
                                                          return function () {
                                                              var t,
                                                                  i = this._super,
                                                                  n = this._superApply;
                                                              return (this._super = o), (this._superApply = s), (t = e.apply(this, arguments)), (this._super = i), (this._superApply = n), t;
                                                          };
                                                      })()
                                                    : e;
                                        }),
                                        (n.prototype = t.widget.extend(r, { widgetEventPrefix: (s && r.widgetEventPrefix) || e }, a, { constructor: n, namespace: l, widgetName: e, widgetFullName: c })),
                                        s
                                            ? (t.each(s._childConstructors, function (e, i) {
                                                  var o = i.prototype;
                                                  t.widget(o.namespace + "." + o.widgetName, n, i._proto);
                                              }),
                                              delete s._childConstructors)
                                            : i._childConstructors.push(n),
                                        t.widget.bridge(e, n),
                                        n
                                    );
                                }),
                                (t.widget.extend = function (e) {
                                    for (var s, n, r = o.call(arguments, 1), a = 0, l = r.length; a < l; a++)
                                        for (s in r[a]) (n = r[a][s]), i.call(r[a], s) && void 0 !== n && (t.isPlainObject(n) ? (e[s] = t.isPlainObject(e[s]) ? t.widget.extend({}, e[s], n) : t.widget.extend({}, n)) : (e[s] = n));
                                    return e;
                                }),
                                (t.widget.bridge = function (e, i) {
                                    var s = i.prototype.widgetFullName || e;
                                    t.fn[e] = function (n) {
                                        var r = "string" == typeof n,
                                            a = o.call(arguments, 1),
                                            l = this;
                                        return (
                                            r
                                                ? this.length || "instance" !== n
                                                    ? this.each(function () {
                                                          var i,
                                                              o = t.data(this, s);
                                                          return "instance" === n
                                                              ? ((l = o), !1)
                                                              : o
                                                              ? "function" != typeof o[n] || "_" === n.charAt(0)
                                                                  ? t.error("no such method '" + n + "' for " + e + " widget instance")
                                                                  : (i = o[n].apply(o, a)) !== o && void 0 !== i
                                                                  ? ((l = i && i.jquery ? l.pushStack(i.get()) : i), !1)
                                                                  : void 0
                                                              : t.error("cannot call methods on " + e + " prior to initialization; attempted to call method '" + n + "'");
                                                      })
                                                    : (l = void 0)
                                                : (a.length && (n = t.widget.extend.apply(null, [n].concat(a))),
                                                  this.each(function () {
                                                      var e = t.data(this, s);
                                                      e ? (e.option(n || {}), e._init && e._init()) : t.data(this, s, new i(n, this));
                                                  })),
                                            l
                                        );
                                    };
                                }),
                                (t.Widget = function () {}),
                                (t.Widget._childConstructors = []),
                                (t.Widget.prototype = {
                                    widgetName: "widget",
                                    widgetEventPrefix: "",
                                    defaultElement: "<div>",
                                    options: { classes: {}, disabled: !1, create: null },
                                    _createWidget: function (i, o) {
                                        (o = t(o || this.defaultElement || this)[0]),
                                            (this.element = t(o)),
                                            (this.uuid = e++),
                                            (this.eventNamespace = "." + this.widgetName + this.uuid),
                                            (this.bindings = t()),
                                            (this.hoverable = t()),
                                            (this.focusable = t()),
                                            (this.classesElementLookup = {}),
                                            o !== this &&
                                                (t.data(o, this.widgetFullName, this),
                                                this._on(!0, this.element, {
                                                    remove: function (t) {
                                                        t.target === o && this.destroy();
                                                    },
                                                }),
                                                (this.document = t(o.style ? o.ownerDocument : o.document || o)),
                                                (this.window = t(this.document[0].defaultView || this.document[0].parentWindow))),
                                            (this.options = t.widget.extend({}, this.options, this._getCreateOptions(), i)),
                                            this._create(),
                                            this.options.disabled && this._setOptionDisabled(this.options.disabled),
                                            this._trigger("create", null, this._getCreateEventData()),
                                            this._init();
                                    },
                                    _getCreateOptions: function () {
                                        return {};
                                    },
                                    _getCreateEventData: t.noop,
                                    _create: t.noop,
                                    _init: t.noop,
                                    destroy: function () {
                                        var e = this;
                                        this._destroy(),
                                            t.each(this.classesElementLookup, function (t, i) {
                                                e._removeClass(i, t);
                                            }),
                                            this.element.off(this.eventNamespace).removeData(this.widgetFullName),
                                            this.widget().off(this.eventNamespace).removeAttr("aria-disabled"),
                                            this.bindings.off(this.eventNamespace);
                                    },
                                    _destroy: t.noop,
                                    widget: function () {
                                        return this.element;
                                    },
                                    option: function (e, i) {
                                        var o,
                                            s,
                                            n,
                                            r = e;
                                        if (0 === arguments.length) return t.widget.extend({}, this.options);
                                        if ("string" == typeof e)
                                            if (((r = {}), (o = e.split(".")), (e = o.shift()), o.length)) {
                                                for (s = r[e] = t.widget.extend({}, this.options[e]), n = 0; n < o.length - 1; n++) (s[o[n]] = s[o[n]] || {}), (s = s[o[n]]);
                                                if (((e = o.pop()), 1 === arguments.length)) return void 0 === s[e] ? null : s[e];
                                                s[e] = i;
                                            } else {
                                                if (1 === arguments.length) return void 0 === this.options[e] ? null : this.options[e];
                                                r[e] = i;
                                            }
                                        return this._setOptions(r), this;
                                    },
                                    _setOptions: function (t) {
                                        var e;
                                        for (e in t) this._setOption(e, t[e]);
                                        return this;
                                    },
                                    _setOption: function (t, e) {
                                        return "classes" === t && this._setOptionClasses(e), (this.options[t] = e), "disabled" === t && this._setOptionDisabled(e), this;
                                    },
                                    _setOptionClasses: function (e) {
                                        var i, o, s;
                                        for (i in e)
                                            (s = this.classesElementLookup[i]),
                                                e[i] !== this.options.classes[i] && s && s.length && ((o = t(s.get())), this._removeClass(s, i), o.addClass(this._classes({ element: o, keys: i, classes: e, add: !0 })));
                                    },
                                    _setOptionDisabled: function (t) {
                                        this._toggleClass(this.widget(), this.widgetFullName + "-disabled", null, !!t),
                                            t && (this._removeClass(this.hoverable, null, "ui-state-hover"), this._removeClass(this.focusable, null, "ui-state-focus"));
                                    },
                                    enable: function () {
                                        return this._setOptions({ disabled: !1 });
                                    },
                                    disable: function () {
                                        return this._setOptions({ disabled: !0 });
                                    },
                                    _classes: function (e) {
                                        var i = [],
                                            o = this;
                                        function s() {
                                            var i = [];
                                            e.element.each(function (e, s) {
                                                t
                                                    .map(o.classesElementLookup, function (t) {
                                                        return t;
                                                    })
                                                    .some(function (t) {
                                                        return t.is(s);
                                                    }) || i.push(s);
                                            }),
                                                o._on(t(i), { remove: "_untrackClassesElement" });
                                        }
                                        function n(n, r) {
                                            var a, l;
                                            for (l = 0; l < n.length; l++)
                                                (a = o.classesElementLookup[n[l]] || t()),
                                                    e.add ? (s(), (a = t(t.uniqueSort(a.get().concat(e.element.get()))))) : (a = t(a.not(e.element).get())),
                                                    (o.classesElementLookup[n[l]] = a),
                                                    i.push(n[l]),
                                                    r && e.classes[n[l]] && i.push(e.classes[n[l]]);
                                        }
                                        return (e = t.extend({ element: this.element, classes: this.options.classes || {} }, e)).keys && n(e.keys.match(/\S+/g) || [], !0), e.extra && n(e.extra.match(/\S+/g) || []), i.join(" ");
                                    },
                                    _untrackClassesElement: function (e) {
                                        var i = this;
                                        t.each(i.classesElementLookup, function (o, s) {
                                            -1 !== t.inArray(e.target, s) && (i.classesElementLookup[o] = t(s.not(e.target).get()));
                                        }),
                                            this._off(t(e.target));
                                    },
                                    _removeClass: function (t, e, i) {
                                        return this._toggleClass(t, e, i, !1);
                                    },
                                    _addClass: function (t, e, i) {
                                        return this._toggleClass(t, e, i, !0);
                                    },
                                    _toggleClass: function (t, e, i, o) {
                                        o = "boolean" == typeof o ? o : i;
                                        var s = "string" == typeof t || null === t,
                                            n = { extra: s ? e : i, keys: s ? t : e, element: s ? this.element : t, add: o };
                                        return n.element.toggleClass(this._classes(n), o), this;
                                    },
                                    _on: function (e, i, o) {
                                        var s,
                                            n = this;
                                        "boolean" != typeof e && ((o = i), (i = e), (e = !1)),
                                            o ? ((i = s = t(i)), (this.bindings = this.bindings.add(i))) : ((o = i), (i = this.element), (s = this.widget())),
                                            t.each(o, function (o, r) {
                                                function a() {
                                                    if (e || (!0 !== n.options.disabled && !t(this).hasClass("ui-state-disabled"))) return ("string" == typeof r ? n[r] : r).apply(n, arguments);
                                                }
                                                "string" != typeof r && (a.guid = r.guid = r.guid || a.guid || t.guid++);
                                                var l = o.match(/^([\w:-]*)\s*(.*)$/),
                                                    c = l[1] + n.eventNamespace,
                                                    p = l[2];
                                                p ? s.on(c, p, a) : i.on(c, a);
                                            });
                                    },
                                    _off: function (e, i) {
                                        (i = (i || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace),
                                            e.off(i),
                                            (this.bindings = t(this.bindings.not(e).get())),
                                            (this.focusable = t(this.focusable.not(e).get())),
                                            (this.hoverable = t(this.hoverable.not(e).get()));
                                    },
                                    _delay: function (t, e) {
                                        function i() {
                                            return ("string" == typeof t ? o[t] : t).apply(o, arguments);
                                        }
                                        var o = this;
                                        return setTimeout(i, e || 0);
                                    },
                                    _hoverable: function (e) {
                                        (this.hoverable = this.hoverable.add(e)),
                                            this._on(e, {
                                                mouseenter: function (e) {
                                                    this._addClass(t(e.currentTarget), null, "ui-state-hover");
                                                },
                                                mouseleave: function (e) {
                                                    this._removeClass(t(e.currentTarget), null, "ui-state-hover");
                                                },
                                            });
                                    },
                                    _focusable: function (e) {
                                        (this.focusable = this.focusable.add(e)),
                                            this._on(e, {
                                                focusin: function (e) {
                                                    this._addClass(t(e.currentTarget), null, "ui-state-focus");
                                                },
                                                focusout: function (e) {
                                                    this._removeClass(t(e.currentTarget), null, "ui-state-focus");
                                                },
                                            });
                                    },
                                    _trigger: function (e, i, o) {
                                        var s,
                                            n,
                                            r = this.options[e];
                                        if (((o = o || {}), ((i = t.Event(i)).type = (e === this.widgetEventPrefix ? e : this.widgetEventPrefix + e).toLowerCase()), (i.target = this.element[0]), (n = i.originalEvent)))
                                            for (s in n) s in i || (i[s] = n[s]);
                                        return this.element.trigger(i, o), !(("function" == typeof r && !1 === r.apply(this.element[0], [i].concat(o))) || i.isDefaultPrevented());
                                    },
                                }),
                                t.each({ show: "fadeIn", hide: "fadeOut" }, function (e, i) {
                                    t.Widget.prototype["_" + e] = function (o, s, n) {
                                        var r;
                                        "string" == typeof s && (s = { effect: s });
                                        var a = s ? (!0 === s || "number" == typeof s ? i : s.effect || i) : e;
                                        "number" == typeof (s = s || {}) ? (s = { duration: s }) : !0 === s && (s = {}),
                                            (r = !t.isEmptyObject(s)),
                                            (s.complete = n),
                                            s.delay && o.delay(s.delay),
                                            r && t.effects && t.effects.effect[a]
                                                ? o[e](s)
                                                : a !== e && o[a]
                                                ? o[a](s.duration, s.easing, n)
                                                : o.queue(function (i) {
                                                      t(this)[e](), n && n.call(o[0]), i();
                                                  });
                                    };
                                }),
                                t.widget
                            );
                        }),
                        void 0 === (n = "function" == typeof o ? o.apply(e, s) : o) || (t.exports = n);
                })();
            },
            285: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(236), i(400), i(624), i(575), i(192), i(464), i(592), i(891)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    return (
                                        t.widget("ui.draggable", t.ui.mouse, {
                                            version: "1.13.2",
                                            widgetEventPrefix: "drag",
                                            options: {
                                                addClasses: !0,
                                                appendTo: "parent",
                                                axis: !1,
                                                connectToSortable: !1,
                                                containment: !1,
                                                cursor: "auto",
                                                cursorAt: !1,
                                                grid: !1,
                                                handle: !1,
                                                helper: "original",
                                                iframeFix: !1,
                                                opacity: !1,
                                                refreshPositions: !1,
                                                revert: !1,
                                                revertDuration: 500,
                                                scope: "default",
                                                scroll: !0,
                                                scrollSensitivity: 20,
                                                scrollSpeed: 20,
                                                snap: !1,
                                                snapMode: "both",
                                                snapTolerance: 20,
                                                stack: !1,
                                                zIndex: !1,
                                                drag: null,
                                                start: null,
                                                stop: null,
                                            },
                                            _create: function () {
                                                "original" === this.options.helper && this._setPositionRelative(), this.options.addClasses && this._addClass("ui-draggable"), this._setHandleClassName(), this._mouseInit();
                                            },
                                            _setOption: function (t, e) {
                                                this._super(t, e), "handle" === t && (this._removeHandleClassName(), this._setHandleClassName());
                                            },
                                            _destroy: function () {
                                                (this.helper || this.element).is(".ui-draggable-dragging") ? (this.destroyOnClear = !0) : (this._removeHandleClassName(), this._mouseDestroy());
                                            },
                                            _mouseCapture: function (e) {
                                                var i = this.options;
                                                return (
                                                    !(this.helper || i.disabled || t(e.target).closest(".ui-resizable-handle").length > 0) &&
                                                    ((this.handle = this._getHandle(e)), !!this.handle && (this._blurActiveElement(e), this._blockFrames(!0 === i.iframeFix ? "iframe" : i.iframeFix), !0))
                                                );
                                            },
                                            _blockFrames: function (e) {
                                                this.iframeBlocks = this.document.find(e).map(function () {
                                                    var e = t(this);
                                                    return t("<div>").css("position", "absolute").appendTo(e.parent()).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()).offset(e.offset())[0];
                                                });
                                            },
                                            _unblockFrames: function () {
                                                this.iframeBlocks && (this.iframeBlocks.remove(), delete this.iframeBlocks);
                                            },
                                            _blurActiveElement: function (e) {
                                                var i = t.ui.safeActiveElement(this.document[0]);
                                                t(e.target).closest(i).length || t.ui.safeBlur(i);
                                            },
                                            _mouseStart: function (e) {
                                                var i = this.options;
                                                return (
                                                    (this.helper = this._createHelper(e)),
                                                    this._addClass(this.helper, "ui-draggable-dragging"),
                                                    this._cacheHelperProportions(),
                                                    t.ui.ddmanager && (t.ui.ddmanager.current = this),
                                                    this._cacheMargins(),
                                                    (this.cssPosition = this.helper.css("position")),
                                                    (this.scrollParent = this.helper.scrollParent(!0)),
                                                    (this.offsetParent = this.helper.offsetParent()),
                                                    (this.hasFixedAncestor =
                                                        this.helper.parents().filter(function () {
                                                            return "fixed" === t(this).css("position");
                                                        }).length > 0),
                                                    (this.positionAbs = this.element.offset()),
                                                    this._refreshOffsets(e),
                                                    (this.originalPosition = this.position = this._generatePosition(e, !1)),
                                                    (this.originalPageX = e.pageX),
                                                    (this.originalPageY = e.pageY),
                                                    i.cursorAt && this._adjustOffsetFromHelper(i.cursorAt),
                                                    this._setContainment(),
                                                    !1 === this._trigger("start", e)
                                                        ? (this._clear(), !1)
                                                        : (this._cacheHelperProportions(),
                                                          t.ui.ddmanager && !i.dropBehaviour && t.ui.ddmanager.prepareOffsets(this, e),
                                                          this._mouseDrag(e, !0),
                                                          t.ui.ddmanager && t.ui.ddmanager.dragStart(this, e),
                                                          !0)
                                                );
                                            },
                                            _refreshOffsets: function (t) {
                                                (this.offset = {
                                                    top: this.positionAbs.top - this.margins.top,
                                                    left: this.positionAbs.left - this.margins.left,
                                                    scroll: !1,
                                                    parent: this._getParentOffset(),
                                                    relative: this._getRelativeOffset(),
                                                }),
                                                    (this.offset.click = { left: t.pageX - this.offset.left, top: t.pageY - this.offset.top });
                                            },
                                            _mouseDrag: function (e, i) {
                                                if ((this.hasFixedAncestor && (this.offset.parent = this._getParentOffset()), (this.position = this._generatePosition(e, !0)), (this.positionAbs = this._convertPositionTo("absolute")), !i)) {
                                                    var o = this._uiHash();
                                                    if (!1 === this._trigger("drag", e, o)) return this._mouseUp(new t.Event("mouseup", e)), !1;
                                                    this.position = o.position;
                                                }
                                                return (this.helper[0].style.left = this.position.left + "px"), (this.helper[0].style.top = this.position.top + "px"), t.ui.ddmanager && t.ui.ddmanager.drag(this, e), !1;
                                            },
                                            _mouseStop: function (e) {
                                                var i = this,
                                                    o = !1;
                                                return (
                                                    t.ui.ddmanager && !this.options.dropBehaviour && (o = t.ui.ddmanager.drop(this, e)),
                                                    this.dropped && ((o = this.dropped), (this.dropped = !1)),
                                                    ("invalid" === this.options.revert && !o) ||
                                                    ("valid" === this.options.revert && o) ||
                                                    !0 === this.options.revert ||
                                                    ("function" == typeof this.options.revert && this.options.revert.call(this.element, o))
                                                        ? t(this.helper).animate(this.originalPosition, parseInt(this.options.revertDuration, 10), function () {
                                                              !1 !== i._trigger("stop", e) && i._clear();
                                                          })
                                                        : !1 !== this._trigger("stop", e) && this._clear(),
                                                    !1
                                                );
                                            },
                                            _mouseUp: function (e) {
                                                return this._unblockFrames(), t.ui.ddmanager && t.ui.ddmanager.dragStop(this, e), this.handleElement.is(e.target) && this.element.trigger("focus"), t.ui.mouse.prototype._mouseUp.call(this, e);
                                            },
                                            cancel: function () {
                                                return this.helper.is(".ui-draggable-dragging") ? this._mouseUp(new t.Event("mouseup", { target: this.element[0] })) : this._clear(), this;
                                            },
                                            _getHandle: function (e) {
                                                return !this.options.handle || !!t(e.target).closest(this.element.find(this.options.handle)).length;
                                            },
                                            _setHandleClassName: function () {
                                                (this.handleElement = this.options.handle ? this.element.find(this.options.handle) : this.element), this._addClass(this.handleElement, "ui-draggable-handle");
                                            },
                                            _removeHandleClassName: function () {
                                                this._removeClass(this.handleElement, "ui-draggable-handle");
                                            },
                                            _createHelper: function (e) {
                                                var i = this.options,
                                                    o = "function" == typeof i.helper,
                                                    s = o ? t(i.helper.apply(this.element[0], [e])) : "clone" === i.helper ? this.element.clone().removeAttr("id") : this.element;
                                                return (
                                                    s.parents("body").length || s.appendTo("parent" === i.appendTo ? this.element[0].parentNode : i.appendTo),
                                                    o && s[0] === this.element[0] && this._setPositionRelative(),
                                                    s[0] === this.element[0] || /(fixed|absolute)/.test(s.css("position")) || s.css("position", "absolute"),
                                                    s
                                                );
                                            },
                                            _setPositionRelative: function () {
                                                /^(?:r|a|f)/.test(this.element.css("position")) || (this.element[0].style.position = "relative");
                                            },
                                            _adjustOffsetFromHelper: function (t) {
                                                "string" == typeof t && (t = t.split(" ")),
                                                    Array.isArray(t) && (t = { left: +t[0], top: +t[1] || 0 }),
                                                    "left" in t && (this.offset.click.left = t.left + this.margins.left),
                                                    "right" in t && (this.offset.click.left = this.helperProportions.width - t.right + this.margins.left),
                                                    "top" in t && (this.offset.click.top = t.top + this.margins.top),
                                                    "bottom" in t && (this.offset.click.top = this.helperProportions.height - t.bottom + this.margins.top);
                                            },
                                            _isRootNode: function (t) {
                                                return /(html|body)/i.test(t.tagName) || t === this.document[0];
                                            },
                                            _getParentOffset: function () {
                                                var e = this.offsetParent.offset(),
                                                    i = this.document[0];
                                                return (
                                                    "absolute" === this.cssPosition &&
                                                        this.scrollParent[0] !== i &&
                                                        t.contains(this.scrollParent[0], this.offsetParent[0]) &&
                                                        ((e.left += this.scrollParent.scrollLeft()), (e.top += this.scrollParent.scrollTop())),
                                                    this._isRootNode(this.offsetParent[0]) && (e = { top: 0, left: 0 }),
                                                    { top: e.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0), left: e.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0) }
                                                );
                                            },
                                            _getRelativeOffset: function () {
                                                if ("relative" !== this.cssPosition) return { top: 0, left: 0 };
                                                var t = this.element.position(),
                                                    e = this._isRootNode(this.scrollParent[0]);
                                                return {
                                                    top: t.top - (parseInt(this.helper.css("top"), 10) || 0) + (e ? 0 : this.scrollParent.scrollTop()),
                                                    left: t.left - (parseInt(this.helper.css("left"), 10) || 0) + (e ? 0 : this.scrollParent.scrollLeft()),
                                                };
                                            },
                                            _cacheMargins: function () {
                                                this.margins = {
                                                    left: parseInt(this.element.css("marginLeft"), 10) || 0,
                                                    top: parseInt(this.element.css("marginTop"), 10) || 0,
                                                    right: parseInt(this.element.css("marginRight"), 10) || 0,
                                                    bottom: parseInt(this.element.css("marginBottom"), 10) || 0,
                                                };
                                            },
                                            _cacheHelperProportions: function () {
                                                this.helperProportions = { width: this.helper.outerWidth(), height: this.helper.outerHeight() };
                                            },
                                            _setContainment: function () {
                                                var e,
                                                    i,
                                                    o,
                                                    s = this.options,
                                                    n = this.document[0];
                                                (this.relativeContainer = null),
                                                    s.containment
                                                        ? "window" !== s.containment
                                                            ? "document" !== s.containment
                                                                ? s.containment.constructor !== Array
                                                                    ? ("parent" === s.containment && (s.containment = this.helper[0].parentNode),
                                                                      (o = (i = t(s.containment))[0]) &&
                                                                          ((e = /(scroll|auto)/.test(i.css("overflow"))),
                                                                          (this.containment = [
                                                                              (parseInt(i.css("borderLeftWidth"), 10) || 0) + (parseInt(i.css("paddingLeft"), 10) || 0),
                                                                              (parseInt(i.css("borderTopWidth"), 10) || 0) + (parseInt(i.css("paddingTop"), 10) || 0),
                                                                              (e ? Math.max(o.scrollWidth, o.offsetWidth) : o.offsetWidth) -
                                                                                  (parseInt(i.css("borderRightWidth"), 10) || 0) -
                                                                                  (parseInt(i.css("paddingRight"), 10) || 0) -
                                                                                  this.helperProportions.width -
                                                                                  this.margins.left -
                                                                                  this.margins.right,
                                                                              (e ? Math.max(o.scrollHeight, o.offsetHeight) : o.offsetHeight) -
                                                                                  (parseInt(i.css("borderBottomWidth"), 10) || 0) -
                                                                                  (parseInt(i.css("paddingBottom"), 10) || 0) -
                                                                                  this.helperProportions.height -
                                                                                  this.margins.top -
                                                                                  this.margins.bottom,
                                                                          ]),
                                                                          (this.relativeContainer = i)))
                                                                    : (this.containment = s.containment)
                                                                : (this.containment = [
                                                                      0,
                                                                      0,
                                                                      t(n).width() - this.helperProportions.width - this.margins.left,
                                                                      (t(n).height() || n.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top,
                                                                  ])
                                                            : (this.containment = [
                                                                  t(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left,
                                                                  t(window).scrollTop() - this.offset.relative.top - this.offset.parent.top,
                                                                  t(window).scrollLeft() + t(window).width() - this.helperProportions.width - this.margins.left,
                                                                  t(window).scrollTop() + (t(window).height() || n.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top,
                                                              ])
                                                        : (this.containment = null);
                                            },
                                            _convertPositionTo: function (t, e) {
                                                e || (e = this.position);
                                                var i = "absolute" === t ? 1 : -1,
                                                    o = this._isRootNode(this.scrollParent[0]);
                                                return {
                                                    top: e.top + this.offset.relative.top * i + this.offset.parent.top * i - ("fixed" === this.cssPosition ? -this.offset.scroll.top : o ? 0 : this.offset.scroll.top) * i,
                                                    left: e.left + this.offset.relative.left * i + this.offset.parent.left * i - ("fixed" === this.cssPosition ? -this.offset.scroll.left : o ? 0 : this.offset.scroll.left) * i,
                                                };
                                            },
                                            _generatePosition: function (t, e) {
                                                var i,
                                                    o,
                                                    s,
                                                    n,
                                                    r = this.options,
                                                    a = this._isRootNode(this.scrollParent[0]),
                                                    l = t.pageX,
                                                    c = t.pageY;
                                                return (
                                                    (a && this.offset.scroll) || (this.offset.scroll = { top: this.scrollParent.scrollTop(), left: this.scrollParent.scrollLeft() }),
                                                    e &&
                                                        (this.containment &&
                                                            (this.relativeContainer
                                                                ? ((o = this.relativeContainer.offset()), (i = [this.containment[0] + o.left, this.containment[1] + o.top, this.containment[2] + o.left, this.containment[3] + o.top]))
                                                                : (i = this.containment),
                                                            t.pageX - this.offset.click.left < i[0] && (l = i[0] + this.offset.click.left),
                                                            t.pageY - this.offset.click.top < i[1] && (c = i[1] + this.offset.click.top),
                                                            t.pageX - this.offset.click.left > i[2] && (l = i[2] + this.offset.click.left),
                                                            t.pageY - this.offset.click.top > i[3] && (c = i[3] + this.offset.click.top)),
                                                        r.grid &&
                                                            ((s = r.grid[1] ? this.originalPageY + Math.round((c - this.originalPageY) / r.grid[1]) * r.grid[1] : this.originalPageY),
                                                            (c = i ? (s - this.offset.click.top >= i[1] || s - this.offset.click.top > i[3] ? s : s - this.offset.click.top >= i[1] ? s - r.grid[1] : s + r.grid[1]) : s),
                                                            (n = r.grid[0] ? this.originalPageX + Math.round((l - this.originalPageX) / r.grid[0]) * r.grid[0] : this.originalPageX),
                                                            (l = i ? (n - this.offset.click.left >= i[0] || n - this.offset.click.left > i[2] ? n : n - this.offset.click.left >= i[0] ? n - r.grid[0] : n + r.grid[0]) : n)),
                                                        "y" === r.axis && (l = this.originalPageX),
                                                        "x" === r.axis && (c = this.originalPageY)),
                                                    {
                                                        top: c - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + ("fixed" === this.cssPosition ? -this.offset.scroll.top : a ? 0 : this.offset.scroll.top),
                                                        left: l - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + ("fixed" === this.cssPosition ? -this.offset.scroll.left : a ? 0 : this.offset.scroll.left),
                                                    }
                                                );
                                            },
                                            _clear: function () {
                                                this._removeClass(this.helper, "ui-draggable-dragging"),
                                                    this.helper[0] === this.element[0] || this.cancelHelperRemoval || this.helper.remove(),
                                                    (this.helper = null),
                                                    (this.cancelHelperRemoval = !1),
                                                    this.destroyOnClear && this.destroy();
                                            },
                                            _trigger: function (e, i, o) {
                                                return (
                                                    (o = o || this._uiHash()),
                                                    t.ui.plugin.call(this, e, [i, o, this], !0),
                                                    /^(drag|start|stop)/.test(e) && ((this.positionAbs = this._convertPositionTo("absolute")), (o.offset = this.positionAbs)),
                                                    t.Widget.prototype._trigger.call(this, e, i, o)
                                                );
                                            },
                                            plugins: {},
                                            _uiHash: function () {
                                                return { helper: this.helper, position: this.position, originalPosition: this.originalPosition, offset: this.positionAbs };
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "connectToSortable", {
                                            start: function (e, i, o) {
                                                var s = t.extend({}, i, { item: o.element });
                                                (o.sortables = []),
                                                    t(o.options.connectToSortable).each(function () {
                                                        var i = t(this).sortable("instance");
                                                        i && !i.options.disabled && (o.sortables.push(i), i.refreshPositions(), i._trigger("activate", e, s));
                                                    });
                                            },
                                            stop: function (e, i, o) {
                                                var s = t.extend({}, i, { item: o.element });
                                                (o.cancelHelperRemoval = !1),
                                                    t.each(o.sortables, function () {
                                                        var t = this;
                                                        t.isOver
                                                            ? ((t.isOver = 0),
                                                              (o.cancelHelperRemoval = !0),
                                                              (t.cancelHelperRemoval = !1),
                                                              (t._storedCSS = { position: t.placeholder.css("position"), top: t.placeholder.css("top"), left: t.placeholder.css("left") }),
                                                              t._mouseStop(e),
                                                              (t.options.helper = t.options._helper))
                                                            : ((t.cancelHelperRemoval = !0), t._trigger("deactivate", e, s));
                                                    });
                                            },
                                            drag: function (e, i, o) {
                                                t.each(o.sortables, function () {
                                                    var s = !1,
                                                        n = this;
                                                    (n.positionAbs = o.positionAbs),
                                                        (n.helperProportions = o.helperProportions),
                                                        (n.offset.click = o.offset.click),
                                                        n._intersectsWith(n.containerCache) &&
                                                            ((s = !0),
                                                            t.each(o.sortables, function () {
                                                                return (
                                                                    (this.positionAbs = o.positionAbs),
                                                                    (this.helperProportions = o.helperProportions),
                                                                    (this.offset.click = o.offset.click),
                                                                    this !== n && this._intersectsWith(this.containerCache) && t.contains(n.element[0], this.element[0]) && (s = !1),
                                                                    s
                                                                );
                                                            })),
                                                        s
                                                            ? (n.isOver ||
                                                                  ((n.isOver = 1),
                                                                  (o._parent = i.helper.parent()),
                                                                  (n.currentItem = i.helper.appendTo(n.element).data("ui-sortable-item", !0)),
                                                                  (n.options._helper = n.options.helper),
                                                                  (n.options.helper = function () {
                                                                      return i.helper[0];
                                                                  }),
                                                                  (e.target = n.currentItem[0]),
                                                                  n._mouseCapture(e, !0),
                                                                  n._mouseStart(e, !0, !0),
                                                                  (n.offset.click.top = o.offset.click.top),
                                                                  (n.offset.click.left = o.offset.click.left),
                                                                  (n.offset.parent.left -= o.offset.parent.left - n.offset.parent.left),
                                                                  (n.offset.parent.top -= o.offset.parent.top - n.offset.parent.top),
                                                                  o._trigger("toSortable", e),
                                                                  (o.dropped = n.element),
                                                                  t.each(o.sortables, function () {
                                                                      this.refreshPositions();
                                                                  }),
                                                                  (o.currentItem = o.element),
                                                                  (n.fromOutside = o)),
                                                              n.currentItem && (n._mouseDrag(e), (i.position = n.position)))
                                                            : n.isOver &&
                                                              ((n.isOver = 0),
                                                              (n.cancelHelperRemoval = !0),
                                                              (n.options._revert = n.options.revert),
                                                              (n.options.revert = !1),
                                                              n._trigger("out", e, n._uiHash(n)),
                                                              n._mouseStop(e, !0),
                                                              (n.options.revert = n.options._revert),
                                                              (n.options.helper = n.options._helper),
                                                              n.placeholder && n.placeholder.remove(),
                                                              i.helper.appendTo(o._parent),
                                                              o._refreshOffsets(e),
                                                              (i.position = o._generatePosition(e, !0)),
                                                              o._trigger("fromSortable", e),
                                                              (o.dropped = !1),
                                                              t.each(o.sortables, function () {
                                                                  this.refreshPositions();
                                                              }));
                                                });
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "cursor", {
                                            start: function (e, i, o) {
                                                var s = t("body"),
                                                    n = o.options;
                                                s.css("cursor") && (n._cursor = s.css("cursor")), s.css("cursor", n.cursor);
                                            },
                                            stop: function (e, i, o) {
                                                var s = o.options;
                                                s._cursor && t("body").css("cursor", s._cursor);
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "opacity", {
                                            start: function (e, i, o) {
                                                var s = t(i.helper),
                                                    n = o.options;
                                                s.css("opacity") && (n._opacity = s.css("opacity")), s.css("opacity", n.opacity);
                                            },
                                            stop: function (e, i, o) {
                                                var s = o.options;
                                                s._opacity && t(i.helper).css("opacity", s._opacity);
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "scroll", {
                                            start: function (t, e, i) {
                                                i.scrollParentNotHidden || (i.scrollParentNotHidden = i.helper.scrollParent(!1)),
                                                    i.scrollParentNotHidden[0] !== i.document[0] && "HTML" !== i.scrollParentNotHidden[0].tagName && (i.overflowOffset = i.scrollParentNotHidden.offset());
                                            },
                                            drag: function (e, i, o) {
                                                var s = o.options,
                                                    n = !1,
                                                    r = o.scrollParentNotHidden[0],
                                                    a = o.document[0];
                                                r !== a && "HTML" !== r.tagName
                                                    ? ((s.axis && "x" === s.axis) ||
                                                          (o.overflowOffset.top + r.offsetHeight - e.pageY < s.scrollSensitivity
                                                              ? (r.scrollTop = n = r.scrollTop + s.scrollSpeed)
                                                              : e.pageY - o.overflowOffset.top < s.scrollSensitivity && (r.scrollTop = n = r.scrollTop - s.scrollSpeed)),
                                                      (s.axis && "y" === s.axis) ||
                                                          (o.overflowOffset.left + r.offsetWidth - e.pageX < s.scrollSensitivity
                                                              ? (r.scrollLeft = n = r.scrollLeft + s.scrollSpeed)
                                                              : e.pageX - o.overflowOffset.left < s.scrollSensitivity && (r.scrollLeft = n = r.scrollLeft - s.scrollSpeed)))
                                                    : ((s.axis && "x" === s.axis) ||
                                                          (e.pageY - t(a).scrollTop() < s.scrollSensitivity
                                                              ? (n = t(a).scrollTop(t(a).scrollTop() - s.scrollSpeed))
                                                              : t(window).height() - (e.pageY - t(a).scrollTop()) < s.scrollSensitivity && (n = t(a).scrollTop(t(a).scrollTop() + s.scrollSpeed))),
                                                      (s.axis && "y" === s.axis) ||
                                                          (e.pageX - t(a).scrollLeft() < s.scrollSensitivity
                                                              ? (n = t(a).scrollLeft(t(a).scrollLeft() - s.scrollSpeed))
                                                              : t(window).width() - (e.pageX - t(a).scrollLeft()) < s.scrollSensitivity && (n = t(a).scrollLeft(t(a).scrollLeft() + s.scrollSpeed)))),
                                                    !1 !== n && t.ui.ddmanager && !s.dropBehaviour && t.ui.ddmanager.prepareOffsets(o, e);
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "snap", {
                                            start: function (e, i, o) {
                                                var s = o.options;
                                                (o.snapElements = []),
                                                    t(s.snap.constructor !== String ? s.snap.items || ":data(ui-draggable)" : s.snap).each(function () {
                                                        var e = t(this),
                                                            i = e.offset();
                                                        this !== o.element[0] && o.snapElements.push({ item: this, width: e.outerWidth(), height: e.outerHeight(), top: i.top, left: i.left });
                                                    });
                                            },
                                            drag: function (e, i, o) {
                                                var s,
                                                    n,
                                                    r,
                                                    a,
                                                    l,
                                                    c,
                                                    p,
                                                    h,
                                                    d,
                                                    u,
                                                    f = o.options,
                                                    m = f.snapTolerance,
                                                    g = i.offset.left,
                                                    _ = g + o.helperProportions.width,
                                                    v = i.offset.top,
                                                    b = v + o.helperProportions.height;
                                                for (d = o.snapElements.length - 1; d >= 0; d--)
                                                    (c = (l = o.snapElements[d].left - o.margins.left) + o.snapElements[d].width),
                                                        (h = (p = o.snapElements[d].top - o.margins.top) + o.snapElements[d].height),
                                                        _ < l - m || g > c + m || b < p - m || v > h + m || !t.contains(o.snapElements[d].item.ownerDocument, o.snapElements[d].item)
                                                            ? (o.snapElements[d].snapping && o.options.snap.release && o.options.snap.release.call(o.element, e, t.extend(o._uiHash(), { snapItem: o.snapElements[d].item })),
                                                              (o.snapElements[d].snapping = !1))
                                                            : ("inner" !== f.snapMode &&
                                                                  ((s = Math.abs(p - b) <= m),
                                                                  (n = Math.abs(h - v) <= m),
                                                                  (r = Math.abs(l - _) <= m),
                                                                  (a = Math.abs(c - g) <= m),
                                                                  s && (i.position.top = o._convertPositionTo("relative", { top: p - o.helperProportions.height, left: 0 }).top),
                                                                  n && (i.position.top = o._convertPositionTo("relative", { top: h, left: 0 }).top),
                                                                  r && (i.position.left = o._convertPositionTo("relative", { top: 0, left: l - o.helperProportions.width }).left),
                                                                  a && (i.position.left = o._convertPositionTo("relative", { top: 0, left: c }).left)),
                                                              (u = s || n || r || a),
                                                              "outer" !== f.snapMode &&
                                                                  ((s = Math.abs(p - v) <= m),
                                                                  (n = Math.abs(h - b) <= m),
                                                                  (r = Math.abs(l - g) <= m),
                                                                  (a = Math.abs(c - _) <= m),
                                                                  s && (i.position.top = o._convertPositionTo("relative", { top: p, left: 0 }).top),
                                                                  n && (i.position.top = o._convertPositionTo("relative", { top: h - o.helperProportions.height, left: 0 }).top),
                                                                  r && (i.position.left = o._convertPositionTo("relative", { top: 0, left: l }).left),
                                                                  a && (i.position.left = o._convertPositionTo("relative", { top: 0, left: c - o.helperProportions.width }).left)),
                                                              !o.snapElements[d].snapping &&
                                                                  (s || n || r || a || u) &&
                                                                  o.options.snap.snap &&
                                                                  o.options.snap.snap.call(o.element, e, t.extend(o._uiHash(), { snapItem: o.snapElements[d].item })),
                                                              (o.snapElements[d].snapping = s || n || r || a || u));
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "stack", {
                                            start: function (e, i, o) {
                                                var s,
                                                    n = o.options,
                                                    r = t.makeArray(t(n.stack)).sort(function (e, i) {
                                                        return (parseInt(t(e).css("zIndex"), 10) || 0) - (parseInt(t(i).css("zIndex"), 10) || 0);
                                                    });
                                                r.length &&
                                                    ((s = parseInt(t(r[0]).css("zIndex"), 10) || 0),
                                                    t(r).each(function (e) {
                                                        t(this).css("zIndex", s + e);
                                                    }),
                                                    this.css("zIndex", s + r.length));
                                            },
                                        }),
                                        t.ui.plugin.add("draggable", "zIndex", {
                                            start: function (e, i, o) {
                                                var s = t(i.helper),
                                                    n = o.options;
                                                s.css("zIndex") && (n._zIndex = s.css("zIndex")), s.css("zIndex", n.zIndex);
                                            },
                                            stop: function (e, i, o) {
                                                var s = o.options;
                                                s._zIndex && t(i.helper).css("zIndex", s._zIndex);
                                            },
                                        }),
                                        t.ui.draggable
                                    );
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            709: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(285), i(236), i(592), i(891)]),
                        (o = function (t) {
                            t.widget("ui.droppable", {
                                version: "1.13.2",
                                widgetEventPrefix: "drop",
                                options: { accept: "*", addClasses: !0, greedy: !1, scope: "default", tolerance: "intersect", activate: null, deactivate: null, drop: null, out: null, over: null },
                                _create: function () {
                                    var t,
                                        e = this.options,
                                        i = e.accept;
                                    (this.isover = !1),
                                        (this.isout = !0),
                                        (this.accept =
                                            "function" == typeof i
                                                ? i
                                                : function (t) {
                                                      return t.is(i);
                                                  }),
                                        (this.proportions = function () {
                                            if (!arguments.length) return t || (t = { width: this.element[0].offsetWidth, height: this.element[0].offsetHeight });
                                            t = arguments[0];
                                        }),
                                        this._addToManager(e.scope),
                                        e.addClasses && this._addClass("ui-droppable");
                                },
                                _addToManager: function (e) {
                                    (t.ui.ddmanager.droppables[e] = t.ui.ddmanager.droppables[e] || []), t.ui.ddmanager.droppables[e].push(this);
                                },
                                _splice: function (t) {
                                    for (var e = 0; e < t.length; e++) t[e] === this && t.splice(e, 1);
                                },
                                _destroy: function () {
                                    var e = t.ui.ddmanager.droppables[this.options.scope];
                                    this._splice(e);
                                },
                                _setOption: function (e, i) {
                                    if ("accept" === e)
                                        this.accept =
                                            "function" == typeof i
                                                ? i
                                                : function (t) {
                                                      return t.is(i);
                                                  };
                                    else if ("scope" === e) {
                                        var o = t.ui.ddmanager.droppables[this.options.scope];
                                        this._splice(o), this._addToManager(i);
                                    }
                                    this._super(e, i);
                                },
                                _activate: function (e) {
                                    var i = t.ui.ddmanager.current;
                                    this._addActiveClass(), i && this._trigger("activate", e, this.ui(i));
                                },
                                _deactivate: function (e) {
                                    var i = t.ui.ddmanager.current;
                                    this._removeActiveClass(), i && this._trigger("deactivate", e, this.ui(i));
                                },
                                _over: function (e) {
                                    var i = t.ui.ddmanager.current;
                                    i && (i.currentItem || i.element)[0] !== this.element[0] && this.accept.call(this.element[0], i.currentItem || i.element) && (this._addHoverClass(), this._trigger("over", e, this.ui(i)));
                                },
                                _out: function (e) {
                                    var i = t.ui.ddmanager.current;
                                    i && (i.currentItem || i.element)[0] !== this.element[0] && this.accept.call(this.element[0], i.currentItem || i.element) && (this._removeHoverClass(), this._trigger("out", e, this.ui(i)));
                                },
                                _drop: function (e, i) {
                                    var o = i || t.ui.ddmanager.current,
                                        s = !1;
                                    return (
                                        !(!o || (o.currentItem || o.element)[0] === this.element[0]) &&
                                        (this.element
                                            .find(":data(ui-droppable)")
                                            .not(".ui-draggable-dragging")
                                            .each(function () {
                                                var i = t(this).droppable("instance");
                                                if (
                                                    i.options.greedy &&
                                                    !i.options.disabled &&
                                                    i.options.scope === o.options.scope &&
                                                    i.accept.call(i.element[0], o.currentItem || o.element) &&
                                                    t.ui.intersect(o, t.extend(i, { offset: i.element.offset() }), i.options.tolerance, e)
                                                )
                                                    return (s = !0), !1;
                                            }),
                                        !s && !!this.accept.call(this.element[0], o.currentItem || o.element) && (this._removeActiveClass(), this._removeHoverClass(), this._trigger("drop", e, this.ui(o)), this.element))
                                    );
                                },
                                ui: function (t) {
                                    return { draggable: t.currentItem || t.element, helper: t.helper, position: t.position, offset: t.positionAbs };
                                },
                                _addHoverClass: function () {
                                    this._addClass("ui-droppable-hover");
                                },
                                _removeHoverClass: function () {
                                    this._removeClass("ui-droppable-hover");
                                },
                                _addActiveClass: function () {
                                    this._addClass("ui-droppable-active");
                                },
                                _removeActiveClass: function () {
                                    this._removeClass("ui-droppable-active");
                                },
                            }),
                                (t.ui.intersect = (function () {
                                    function t(t, e, i) {
                                        return t >= e && t < e + i;
                                    }
                                    return function (e, i, o, s) {
                                        if (!i.offset) return !1;
                                        var n = (e.positionAbs || e.position.absolute).left + e.margins.left,
                                            r = (e.positionAbs || e.position.absolute).top + e.margins.top,
                                            a = n + e.helperProportions.width,
                                            l = r + e.helperProportions.height,
                                            c = i.offset.left,
                                            p = i.offset.top,
                                            h = c + i.proportions().width,
                                            d = p + i.proportions().height;
                                        switch (o) {
                                            case "fit":
                                                return c <= n && a <= h && p <= r && l <= d;
                                            case "intersect":
                                                return c < n + e.helperProportions.width / 2 && a - e.helperProportions.width / 2 < h && p < r + e.helperProportions.height / 2 && l - e.helperProportions.height / 2 < d;
                                            case "pointer":
                                                return t(s.pageY, p, i.proportions().height) && t(s.pageX, c, i.proportions().width);
                                            case "touch":
                                                return ((r >= p && r <= d) || (l >= p && l <= d) || (r < p && l > d)) && ((n >= c && n <= h) || (a >= c && a <= h) || (n < c && a > h));
                                            default:
                                                return !1;
                                        }
                                    };
                                })()),
                                (t.ui.ddmanager = {
                                    current: null,
                                    droppables: { default: [] },
                                    prepareOffsets: function (e, i) {
                                        var o,
                                            s,
                                            n = t.ui.ddmanager.droppables[e.options.scope] || [],
                                            r = i ? i.type : null,
                                            a = (e.currentItem || e.element).find(":data(ui-droppable)").addBack();
                                        t: for (o = 0; o < n.length; o++)
                                            if (!(n[o].options.disabled || (e && !n[o].accept.call(n[o].element[0], e.currentItem || e.element)))) {
                                                for (s = 0; s < a.length; s++)
                                                    if (a[s] === n[o].element[0]) {
                                                        n[o].proportions().height = 0;
                                                        continue t;
                                                    }
                                                (n[o].visible = "none" !== n[o].element.css("display")),
                                                    n[o].visible &&
                                                        ("mousedown" === r && n[o]._activate.call(n[o], i),
                                                        (n[o].offset = n[o].element.offset()),
                                                        n[o].proportions({ width: n[o].element[0].offsetWidth, height: n[o].element[0].offsetHeight }));
                                            }
                                    },
                                    drop: function (e, i) {
                                        var o = !1;
                                        return (
                                            t.each((t.ui.ddmanager.droppables[e.options.scope] || []).slice(), function () {
                                                this.options &&
                                                    (!this.options.disabled && this.visible && t.ui.intersect(e, this, this.options.tolerance, i) && (o = this._drop.call(this, i) || o),
                                                    !this.options.disabled && this.visible && this.accept.call(this.element[0], e.currentItem || e.element) && ((this.isout = !0), (this.isover = !1), this._deactivate.call(this, i)));
                                            }),
                                            o
                                        );
                                    },
                                    dragStart: function (e, i) {
                                        e.element.parentsUntil("body").on("scroll.droppable", function () {
                                            e.options.refreshPositions || t.ui.ddmanager.prepareOffsets(e, i);
                                        });
                                    },
                                    drag: function (e, i) {
                                        e.options.refreshPositions && t.ui.ddmanager.prepareOffsets(e, i),
                                            t.each(t.ui.ddmanager.droppables[e.options.scope] || [], function () {
                                                if (!this.options.disabled && !this.greedyChild && this.visible) {
                                                    var o,
                                                        s,
                                                        n,
                                                        r = t.ui.intersect(e, this, this.options.tolerance, i),
                                                        a = !r && this.isover ? "isout" : r && !this.isover ? "isover" : null;
                                                    a &&
                                                        (this.options.greedy &&
                                                            ((s = this.options.scope),
                                                            (n = this.element.parents(":data(ui-droppable)").filter(function () {
                                                                return t(this).droppable("instance").options.scope === s;
                                                            })).length && ((o = t(n[0]).droppable("instance")).greedyChild = "isover" === a)),
                                                        o && "isover" === a && ((o.isover = !1), (o.isout = !0), o._out.call(o, i)),
                                                        (this[a] = !0),
                                                        (this["isout" === a ? "isover" : "isout"] = !1),
                                                        this["isover" === a ? "_over" : "_out"].call(this, i),
                                                        o && "isout" === a && ((o.isout = !1), (o.isover = !0), o._over.call(o, i)));
                                                }
                                            });
                                    },
                                    dragStop: function (e, i) {
                                        e.element.parentsUntil("body").off("scroll.droppable"), e.options.refreshPositions || t.ui.ddmanager.prepareOffsets(e, i);
                                    },
                                }),
                                !1 !== t.uiBackCompat &&
                                    t.widget("ui.droppable", t.ui.droppable, {
                                        options: { hoverClass: !1, activeClass: !1 },
                                        _addActiveClass: function () {
                                            this._super(), this.options.activeClass && this.element.addClass(this.options.activeClass);
                                        },
                                        _removeActiveClass: function () {
                                            this._super(), this.options.activeClass && this.element.removeClass(this.options.activeClass);
                                        },
                                        _addHoverClass: function () {
                                            this._super(), this.options.hoverClass && this.element.addClass(this.options.hoverClass);
                                        },
                                        _removeHoverClass: function () {
                                            this._super(), this.options.hoverClass && this.element.removeClass(this.options.hoverClass);
                                        },
                                    });
                            return t.ui.droppable;
                        }),
                        void 0 === (n = "function" == typeof o ? o.apply(e, s) : o) || (t.exports = n);
                })();
            },
            236: function (t, e, i) {
                var o, s, n;
                !(function (r) {
                    "use strict";
                    (s = [i(311), i(870), i(592), i(891)]),
                        void 0 ===
                            (n =
                                "function" ==
                                typeof (o = function (t) {
                                    var e = !1;
                                    return (
                                        t(document).on("mouseup", function () {
                                            e = !1;
                                        }),
                                        t.widget("ui.mouse", {
                                            version: "1.13.2",
                                            options: { cancel: "input, textarea, button, select, option", distance: 1, delay: 0 },
                                            _mouseInit: function () {
                                                var e = this;
                                                this.element
                                                    .on("mousedown." + this.widgetName, function (t) {
                                                        return e._mouseDown(t);
                                                    })
                                                    .on("click." + this.widgetName, function (i) {
                                                        if (!0 === t.data(i.target, e.widgetName + ".preventClickEvent")) return t.removeData(i.target, e.widgetName + ".preventClickEvent"), i.stopImmediatePropagation(), !1;
                                                    }),
                                                    (this.started = !1);
                                            },
                                            _mouseDestroy: function () {
                                                this.element.off("." + this.widgetName),
                                                    this._mouseMoveDelegate && this.document.off("mousemove." + this.widgetName, this._mouseMoveDelegate).off("mouseup." + this.widgetName, this._mouseUpDelegate);
                                            },
                                            _mouseDown: function (i) {
                                                if (!e) {
                                                    (this._mouseMoved = !1), this._mouseStarted && this._mouseUp(i), (this._mouseDownEvent = i);
                                                    var o = this,
                                                        s = 1 === i.which,
                                                        n = !("string" != typeof this.options.cancel || !i.target.nodeName) && t(i.target).closest(this.options.cancel).length;
                                                    return (
                                                        !(s && !n && this._mouseCapture(i)) ||
                                                        ((this.mouseDelayMet = !this.options.delay),
                                                        this.mouseDelayMet ||
                                                            (this._mouseDelayTimer = setTimeout(function () {
                                                                o.mouseDelayMet = !0;
                                                            }, this.options.delay)),
                                                        this._mouseDistanceMet(i) && this._mouseDelayMet(i) && ((this._mouseStarted = !1 !== this._mouseStart(i)), !this._mouseStarted)
                                                            ? (i.preventDefault(), !0)
                                                            : (!0 === t.data(i.target, this.widgetName + ".preventClickEvent") && t.removeData(i.target, this.widgetName + ".preventClickEvent"),
                                                              (this._mouseMoveDelegate = function (t) {
                                                                  return o._mouseMove(t);
                                                              }),
                                                              (this._mouseUpDelegate = function (t) {
                                                                  return o._mouseUp(t);
                                                              }),
                                                              this.document.on("mousemove." + this.widgetName, this._mouseMoveDelegate).on("mouseup." + this.widgetName, this._mouseUpDelegate),
                                                              i.preventDefault(),
                                                              (e = !0),
                                                              !0))
                                                    );
                                                }
                                            },
                                            _mouseMove: function (e) {
                                                if (this._mouseMoved) {
                                                    if (t.ui.ie && (!document.documentMode || document.documentMode < 9) && !e.button) return this._mouseUp(e);
                                                    if (!e.which)
                                                        if (e.originalEvent.altKey || e.originalEvent.ctrlKey || e.originalEvent.metaKey || e.originalEvent.shiftKey) this.ignoreMissingWhich = !0;
                                                        else if (!this.ignoreMissingWhich) return this._mouseUp(e);
                                                }
                                                return (
                                                    (e.which || e.button) && (this._mouseMoved = !0),
                                                    this._mouseStarted
                                                        ? (this._mouseDrag(e), e.preventDefault())
                                                        : (this._mouseDistanceMet(e) &&
                                                              this._mouseDelayMet(e) &&
                                                              ((this._mouseStarted = !1 !== this._mouseStart(this._mouseDownEvent, e)), this._mouseStarted ? this._mouseDrag(e) : this._mouseUp(e)),
                                                          !this._mouseStarted)
                                                );
                                            },
                                            _mouseUp: function (i) {
                                                this.document.off("mousemove." + this.widgetName, this._mouseMoveDelegate).off("mouseup." + this.widgetName, this._mouseUpDelegate),
                                                    this._mouseStarted && ((this._mouseStarted = !1), i.target === this._mouseDownEvent.target && t.data(i.target, this.widgetName + ".preventClickEvent", !0), this._mouseStop(i)),
                                                    this._mouseDelayTimer && (clearTimeout(this._mouseDelayTimer), delete this._mouseDelayTimer),
                                                    (this.ignoreMissingWhich = !1),
                                                    (e = !1),
                                                    i.preventDefault();
                                            },
                                            _mouseDistanceMet: function (t) {
                                                return Math.max(Math.abs(this._mouseDownEvent.pageX - t.pageX), Math.abs(this._mouseDownEvent.pageY - t.pageY)) >= this.options.distance;
                                            },
                                            _mouseDelayMet: function () {
                                                return this.mouseDelayMet;
                                            },
                                            _mouseStart: function () {},
                                            _mouseDrag: function () {},
                                            _mouseStop: function () {},
                                            _mouseCapture: function () {
                                                return !0;
                                            },
                                        })
                                    );
                                })
                                    ? o.apply(e, s)
                                    : o) || (t.exports = n);
                })();
            },
            311: function (t) {
                "use strict";
                t.exports = jQuery;
            },
        },
        e = {};
    function i(o) {
        var s = e[o];
        if (void 0 !== s) return s.exports;
        var n = (e[o] = { exports: {} });
        return t[o](n, n.exports, i), n.exports;
    }
    (i.n = function (t) {
        var e =
            t && t.__esModule
                ? function () {
                      return t.default;
                  }
                : function () {
                      return t;
                  };
        return i.d(e, { a: e }), e;
    }),
        (i.d = function (t, e) {
            for (var o in e) i.o(e, o) && !i.o(t, o) && Object.defineProperty(t, o, { enumerable: !0, get: e[o] });
        }),
        (i.o = function (t, e) {
            return Object.prototype.hasOwnProperty.call(t, e);
        }),
        (function () {
            "use strict";
            i(709), i(266), i(916);
        })();
})();
