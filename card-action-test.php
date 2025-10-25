<?php require_once 'header.php'; ?>


<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">



        <!-- ****************************************************************************************************************
****************************************************************************************************************
**************************************************************************************************************** -->
        
        <div class="card mb-6 p-0">
            <div class="card-header">
                <div id="response" class="  mt-3"></div>
                 <form id="myform" class=" p-4 " data-table="feedbacks">
                <h4 class="mb-3">তথ্য ইনপুট</h4>

                <input type="text" name="id" value="0"> <!-- Hidden id field -->

                <div class="mb-3">
                    <label class="form-label">নাম:</label>
                    <input type="text" name="rating" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">নাম:</label>
                    <input type="text" name="feedback" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">নাম:</label>
                    <input type="date" name="dob" class="form-control" data-ignore="1">
                </div>


                <button type="submit" class="btn btn-primary">সাবমিট</button>
            </form>
            </div>
           
        </div>




        <!-- ****************************************************************************************************************
****************************************************************************************************************
**************************************************************************************************************** -->




        <!-- Cards Action -->
        <div class="card card-action mb-6">
            <div class="card-alert"></div>
            <div class="card-header">
                <h5 class="card-action-title mb-0">Cards Action</h5>
                <div class="card-action-element">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="card-collapsible"><i
                                    class="icon-base ri ri-arrow-up-s-line icon-sm"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="card-action card-reload" data-action="reload"
                                data-fetch="fetch.php" data-id="1">
                                <span class="d-inline-flex scaleX-n1-rtl align-middle">
                                    <i class="icon-base ri ri-refresh-line icon-sm"></i>
                                </span>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="card-expand"><i
                                    class="icon-base ri ri-fullscreen-fill icon-sm"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="card-close"><i
                                    class="icon-base ri ri-close-fill icon-sm"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-content collapse show">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th class="text-center">Icon</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <tr>
                            <td>Collapse</td>
                            <td class="text-center">
                                <i class="icon-base ri ri-arrow-up-s-line icon-sm"></i>
                            </td>
                            <td>Collapse card content using collapse action.</td>
                        </tr>
                        <tr>
                            <td>Refresh Content</td>
                            <td class="text-center scaleX-n1-rtl">
                                <i class="icon-base ri ri-refresh-line icon-sm"></i>
                            </td>
                            <td>Refresh your card content using refresh action.</td>
                        </tr>
                        <tr>
                            <td>Expand Card</td>
                            <td class="text-center">
                                <i class="icon-base ri ri-fullscreen-fill icon-sm"></i>
                            </td>
                            <td>Maximize your card using expand action</td>
                        </tr>
                        <tr>
                            <td>Remove Card</td>
                            <td class="text-center">
                                <i class="icon-base ri ri-close-fill icon-sm"></i>
                            </td>
                            <td>Remove card from page using remove card action</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <h6 class="mb-6 text-body-secondary">Examples</h6>
        <p>
            Use <code>.card-action</code> class with
            <code>.card</code> class to create action card. Use
            <code>.card-action-title</code> for action card title and
            <code>.card-action-element</code> to warp the actions icons.
        </p>
        <div class="row mb-12">
            <div class="col-md">
                <div class="card card-action mb-6">
                    <div class="card-header">
                        <h5 class="card-action-title mb-0">Collapsible Card</h5>
                        <div class="card-action-element">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="javascript:void(0);" class="card-collapsible"><i
                                            class="icon-base ri ri-arrow-up-s-line icon-sm"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse show">
                        <div class="card-body">
                            <p class="card-text">
                                To create a collapsible card, use
                                <code>.card-collapsible</code> class with action item.
                                To show the collapsible content default use
                                <code>.show</code> class with <code>.collapse</code>.
                            </p>
                            <p class="card-text">
                                Click on
                                <i class="icon-base ri ri-arrow-up-s-line icon-sm"></i>
                                to see card collapse in action.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-action mb-6">
                    <div class="card-alert"></div>
                    <div class="card-header">
                        <h5 class="card-action-title mb-0">Refresh Content</h5>
                        <div class="card-action-element">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="javascript:void(0);" class="card-reload d-flex scaleX-n1-rtl"
                                        data-action="reload" data-fetch="fetch.php" , data-id="35">
                                        <i class="icon-base ri ri-refresh-line icon-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body card-content">
                        <p class="card-text">
                            To create a card with refresh action, use
                            <code>.card-reload</code> class with action item. Use
                            <code>.card-alert</code> class to show custom response
                            message.
                        </p>
                        <p class="card-text">
                            Click on
                            <i class="icon-base ri ri-refresh-line icon-sm scaleX-n1-rtl"></i>
                            icon to see refresh card content in action.
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-md">
                <div class="card card-action mb-6">
                    <div class="card-header">
                        <h5 class="card-action-title mb-0">Expand Card</h5>
                        <div class="card-action-element">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="javascript:void(0);" class="card-expand"><i
                                            class="icon-base ri ri-fullscreen-fill icon-sm"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body">
                        <p class="card-text">
                            To create a card with expand(fullscreen) action, use
                            <code>.card-expand</code> class with action item. Use
                            <kbd>ESC</kbd> key to exit from the fullscreen mode.
                        </p>
                        <p class="card-text">
                            Click on
                            <i class="icon-base ri ri-fullscreen-fill icon-sm"></i>
                            icon to see expand card in action.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-action mb-6">
                    <div class="card-alert"></div>
                    <div class="card-header">
                        <h5 class="card-action-title mb-0">Remove Card</h5>
                        <div class="card-action-element">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="javascript:void(0);" class="card-close"><i
                                            class="icon-base ri ri-close-fill icon-sm"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Remove card action hide the card, use
                            <code>.card-close</code> class with action item.
                        </p>
                        <br />
                        <p class="card-text">
                            Click on
                            <i class="icon-base ri ri-close-fill icon-sm"></i> icon
                            to see remove card in action.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Cards Action -->

        <!-- Header elements -->
        <h5 class="mb-6">Header Elements</h5>
        <div class="row mb-12">
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">Card Header</h5>
                        <div class="card-header-elements ms-auto">
                            <span class="badge bg-primary rounded-pill">New</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Sample card header with badge.</p>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="card-title header-elements">
                            <h5 class="m-0 me-2">Card Title</h5>
                            <div class="card-title-elements ms-auto">
                                <span class="badge badge-outline-primary rounded-pill">10</span>
                            </div>
                        </div>
                        <p class="card-text">
                            Sample card title with outline badge.
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>

            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">Card Header</h5>

                        <div class="card-header-elements ms-auto">
                            <button type="button" class="btn btn-xs btn-primary">
                                <span class="icon-base ri ri-add-fill icon-sm me-1"></span>Button
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Sample card header with extra small button.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="card-title header-elements">
                            <h5 class="m-0 me-2">Card Title</h5>
                            <div class="card-title-elements ms-auto">
                                <button type="button" class="btn btn-icon btn-sm btn-primary">
                                    <span class="icon-base ri ri-shopping-bag-3-line icon-18px"></span>
                                </button>
                            </div>
                        </div>
                        <p class="card-text">
                            Sample card title with small icon button.
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>

            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">Card Header</h5>

                        <div class="card-header-elements ms-auto">
                            <input type="text" class="form-control form-control-sm" placeholder="Search" />
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Sample card header with extra search input box.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="card-title header-elements">
                            <h5 class="m-0 me-2">Card Title</h5>
                            <div class="card-title-elements ms-auto">
                                <div class="form-check form-switch mb-0 me-n2">
                                    <input type="checkbox" class="form-check-input" />
                                </div>
                            </div>
                        </div>
                        <p class="card-text">Sample card title with switch.</p>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">Card Header</h5>
                        <div class="card-header-elements ms-auto">
                            <span class="icon-base ri ri-notification-2-line icon-sm text-body-secondary"></span>
                            <span class="text text-body-secondary d-flex">
                                <small>Sample Text</small>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Sample card header with text.</p>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="card-title header-elements">
                            <h5 class="m-0 me-2">Card Title</h5>
                            <div class="card-header-elements ms-auto">
                                <span class="icon-base ri ri-notification-2-line icon-sm text-body-secondary"></span>
                                <span class="text text-body-secondary d-flex">
                                    <small>Sample Text</small>
                                </span>
                            </div>
                        </div>
                        <p class="card-text">Sample card title with text.</p>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-header header-elements">
                        <h5 class="mb-0 me-2">Card Header</h5>
                        <div class="card-header-elements">
                            <span class="badge bg-danger rounded-pill">Hello!</span>
                        </div>
                        <div class="card-header-elements ms-auto">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary">
                                    Primary
                                </button>
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" data-bs-reference="parent"></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                    <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                    <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Sample card header with badge and dropdown.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="card-title header-elements">
                            <h5 class="m-0 me-2">Card Title</h5>
                            <div class="card-title-elements">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input" />
                                </div>
                            </div>
                            <div class="card-title-elements ms-auto">
                                <select class="form-select form-select-sm">
                                    <option selected="">Option 1</option>
                                    <option>Option 2</option>
                                    <option>Option 3</option>
                                </select>
                            </div>
                        </div>
                        <p class="card-text">
                            Sample card title with switch, select box & button.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header elements -->

        <!-- Draggable Cards -->
        <h5 class="pb-1 mb-6">Draggable Cards</h5>
        <div class="row g-6" id="sortable-4">
            <div class="col-md-6 col-xl-4">
                <div class="card bg-primary text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">Primary card title</h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card bg-secondary text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">
                            Secondary card title
                        </h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card bg-success text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">Success card title</h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card bg-danger text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">Danger card title</h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card bg-warning text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">Warning card title</h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card bg-info text-white">
                    <div class="card-header cursor-move text-white">
                        Drag me!
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-white">Info card title</h5>
                        <p class="card-text">
                            Some quick example text to build on the card title and
                            make up.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Draggable Cards -->
    </div>
    <!-- / Content -->

    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl">
            <div
                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                    &#169;
                    <script>
                        document.write(new Date().getFullYear());
                    </script>
                    , made with ❤️ by
                    <a href="https://themeselection.com/" target="_blank"
                        class="footer-link fw-medium">ThemeSelection</a>
                </div>
                <div class="d-none d-lg-inline-block">
                    <a href="https://themeselection.com/item/category/admin-templates/" target="_blank"
                        class="footer-link me-4">Admin Templates</a>

                    <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>

                    <a href="https://themeselection.com/item/category/bootstrap-templates/" target="_blank"
                        class="footer-link me-4">Bootstrap Dashboard</a>
                    <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/documentation/"
                        target="_blank" class="footer-link me-4">Documentation</a>

                    <a href="https://themeselection.com/support/" target="_blank"
                        class="footer-link d-none d-sm-inline-block">Support</a>
                </div>
            </div>
        </div>
    </footer>
    <!-- / Footer -->

    <div class="content-backdrop fade"></div>
</div>


<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>

<!-- Drag Target Area To SlideIn Menu On Small Screens -->
<div class="drag-target"></div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>


</script>
<!-- ----------------------------------- -->
</body>

</html>