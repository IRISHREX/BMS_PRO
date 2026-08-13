<div id="addonModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addonModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                <h4 class="modal-title" id="addonModalLabel">
                    Register Your Addon
                </h4>
            </div>

            <form action="<?php echo site_url('admin/admin/updateaddon') ?>" method="POST" id="addon_verify">

                <div class="modal-body addon_modal-body">

                    <div class="error_message"></div>

                    <input type="hidden" name="addon" class="addon_type" value="">
                    <input type="hidden" name="addon_version" class="addon_version" value="0">

                    <!-- License Key -->
                    <div class="form-group">
                        <label for="input-app-envato_market_purchase_code">
                            Smart Hospital Addon License Key
                           
                        </label>

                        <input type="text"
                               class="form-control"
                               id="input-app-envato_market_purchase_code"
                               name="app-envato_market_purchase_code"
                               placeholder="Enter your Smart Hospital license key"
                               autocomplete="off"
                               >

                        <div id="error" class="text-danger"></div>
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
                               >

                        <div id="error" class="text-danger"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
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