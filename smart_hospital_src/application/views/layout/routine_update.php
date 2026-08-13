<div id="activelicmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="activelicmodalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

                <h4 class="modal-title" id="activelicmodalLabel">
                    Register Your Purchase Code
                </h4>
            </div>

            <!-- Form -->
            <form action="<?php echo site_url('admin/admin/updatePurchaseCode') ?>"
                  method="POST"
                  id="purchase_code">

                <div class="modal-body lic_modal-body">

                    <!-- Important Notice -->
                    <div class="form-group">
                        <div class="alert alert-info" style="margin-bottom: 15px;">
                            <strong>Important:</strong>

                            Smart Hospital Regular License allows use for a single
                            hospital/branch/end client. For convenience, you can
                            register the purchase code on up to <strong>3 URLs</strong>:

                            <br>1. Localhost
                            <br>2. Testing Environment
                            <br>3. Production URL

                            <br><br>
                            <small>
                                (Testing and production URLs should be on the same domain.)
                            </small>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div class="error_message"></div>

                    <!-- License Key -->
                    <div class="form-group">
                        <label for="input-purchase_code">
                            Smart Hospital License Key                             
                        </label>

                        <input type="text"
                               class="form-control"
                               id="input-purchase_code"
                               name="purchase_code"
                               placeholder="Enter your Smart Hospital license key"
                               autocomplete="off"
                               required>

                        <div id="purchase_code_error" class="text-danger small"></div>
                    </div>

                    <!-- Signing Key -->
                    <div class="form-group">
                        <label for="input-signing_key">
                            Signing Key
                        </label>

                        <input type="text"
                               class="form-control"
                               id="input-signing_key"
                               name="signing_key"
                               placeholder="Enter your signing key"
                               autocomplete="off"
                               required>

                        <div id="signing_key_error" class="text-danger small"></div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        <i class="fa fa-times" aria-hidden="true"></i> <?php echo $this->lang->line('cancel'); ?>
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('please_wait'); ?>">
                        <i class="fa fa-check" aria-hidden="true"></i> Register Now
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>