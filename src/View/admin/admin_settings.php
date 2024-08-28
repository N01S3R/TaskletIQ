<?php App\Helpers\Template::partials('header_admin'); ?>

<div id="app" class="container my-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Admin</a></li>
                <li class="breadcrumb-item"><a href="#">{{ pageTitle }}</a></li>
            </ol>
        </nav>
        <header class="d-flex justify-content-end align-items-center m-4">
            <div>
                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Pulpit
                </a>
            </div>
        </header>
        <div class="row gutters-sm">
            <div class="col-md-4 d-none d-md-block">
                <div class="card">
                    <div class="card-body">
                        <nav class="nav flex-column nav-pills nav-gap-y-1">
                            <a href="#profile" data-bs-toggle="tab" class="nav-item nav-link has-icon nav-link-faded active">
                                <i class="feather feather-user mr-2"></i> Page Settings
                            </a>
                            <a href="#account" data-bs-toggle="tab" class="nav-item nav-link has-icon nav-link-faded">
                                <i class="feather feather-settings mr-2"></i> Account Settings
                            </a>
                            <a href="#security" data-bs-toggle="tab" class="nav-item nav-link has-icon nav-link-faded">
                                <i class="feather feather-shield mr-2"></i> Security
                            </a>
                            <a href="#notification" data-bs-toggle="tab" class="nav-item nav-link has-icon nav-link-faded">
                                <i class="feather feather-bell mr-2"></i> Notification
                            </a>
                            <a href="#billing" data-bs-toggle="tab" class="nav-item nav-link has-icon nav-link-faded">
                                <i class="feather feather-credit-card mr-2"></i> Billing
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card" style="max-height: 600px; overflow: hidden;">
                    <div class="card-body tab-content" id="tabContent">
                        <div class="tab-pane active" ref="tabs" id="profile">
                            <h6>PAGE SETTINGS</h6>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="pageTitle" class="form-label">Page Title</label>
                                    <input type="text" class="form-control" id="pageTitle" placeholder="Enter your page title" value="Default Title">
                                    <div class="form-text">The title of your page. This will appear in the browser tab and as the main heading of your page.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pageDescription" class="form-label">Page Description</label>
                                    <textarea class="form-control" id="pageDescription" placeholder="Write a brief description of your page" rows="3">This is a brief description of your page.</textarea>
                                    <div class="form-text">This description will appear in search engine results and when sharing the page on social media.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pageKeywords" class="form-label">Page Keywords</label>
                                    <input type="text" class="form-control" id="pageKeywords" placeholder="Enter keywords separated by commas" value="keyword1, keyword2, keyword3">
                                    <div class="form-text">Keywords that describe the content of your page. These help search engines understand what your page is about.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pageURL" class="form-label">Page URL</label>
                                    <input type="url" class="form-control" id="pageURL" placeholder="Enter your page URL" value="http://example.com/your-page">
                                    <div class="form-text">The URL where your page can be accessed. Make sure it is correct.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pageLanguage" class="form-label">Page Language</label>
                                    <input type="text" class="form-control" id="pageLanguage" placeholder="Enter the language of your page" value="en">
                                    <div class="form-text">The language of your page content, e.g., 'en' for English, 'es' for Spanish.</div>
                                </div>
                                <div class="text-muted small mb-3">
                                    All of the fields on this page are optional and can be updated at any time. By filling them out, you're giving us consent to use this data for SEO purposes.
                                </div>
                                <button type="button" class="btn btn-primary">Update Page Settings</button>
                                <button type="reset" class="btn btn-light">Reset Changes</button>
                            </form>
                        </div>

                        <div class="tab-pane" id="account">
                            <h6>ACCOUNT SETTINGS</h6>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" placeholder="Enter your username" value="kennethvaldez">
                                    <div class="form-text">After changing your username, your old username becomes available for anyone else to claim.</div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label text-danger">Delete Account</label>
                                    <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                                    <button type="button" class="btn btn-danger">Delete Account</button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="security">
                            <h6>SECURITY SETTINGS</h6>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="oldPassword" class="form-label">Change Password</label>
                                    <input type="password" class="form-control mb-2" id="oldPassword" placeholder="Enter your old password">
                                    <input type="password" class="form-control mb-2" id="newPassword" placeholder="New password">
                                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                                </div>
                            </form>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Two Factor Authentication</label>
                                    <button type="button" class="btn btn-info">Enable two-factor authentication</button>
                                    <p class="small text-muted mt-2">Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.</p>
                                </div>
                            </form>
                            <hr>
                            <form>
                                <div class="mb-0">
                                    <label class="form-label">Sessions</label>
                                    <p class="text-secondary">This is a list of devices that have logged into your account. Revoke any sessions that you do not recognize.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">San Francisco City 190.24.335.55</h6>
                                                <small class="text-muted">Your current session seen in United States</small>
                                            </div>
                                            <button type="button" class="btn btn-light btn-sm">More info</button>
                                        </li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="notification">
                            <h6>NOTIFICATION SETTINGS</h6>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label class="d-block mb-0">Security Alerts</label>
                                    <div class="small text-muted mb-2">Receive security alert notifications via email</div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="securityEmail" checked>
                                        <label class="form-check-label" for="securityEmail">Email</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="securitySMS">
                                        <label class="form-check-label" for="securitySMS">SMS</label>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="d-block mb-0">Push Notifications</label>
                                    <div class="small text-muted mb-2">Receive push notifications for account activities</div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushAll">
                                        <label class="form-check-label" for="pushAll">All Notifications</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushEmail">
                                        <label class="form-check-label" for="pushEmail">Email Notifications</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pushSMS">
                                        <label class="form-check-label" for="pushSMS">SMS Notifications</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="billing">
                            <h6>BILLING SETTINGS</h6>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 1234 5678" value="1234 5678 1234 5678">
                                </div>
                                <div class="mb-3">
                                    <label for="expiryDate" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" value="10/25">
                                </div>
                                <div class="mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123" value="123">
                                </div>
                                <button type="button" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lobibox/js/lobibox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>

<script>
    new Vue({
        el: '#app',
        data: {
            pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
            loading: true,
        },
        mounted() {
            this.loading = false;
            const tabContent = document.getElementById('tabContent');
            new PerfectScrollbar(tabContent, {
                wheelSpeed: 0.5,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        }
    });
</script>

</body>

</html>