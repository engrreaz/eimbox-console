<style>
    .upgrade-icon {
        font-size: 56px;
    }

    .pricing-card {
        border-radius: 16px;
        border: 1px solid #eee;
        transition: all .25s ease;
    }

    .pricing-card:hover {
        transform: translateY(-6px);
    }

    .pricing-card.featured {
        border: 2px solid #0d6efd;
    }

    .progress {
        overflow: hidden;
    }
</style>



<div class="container-xxl flex-grow-1 container-p-y" id="upgrade-container">

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">

            <!-- Header -->
            <div class="text-center mb-4">
                <div class="upgrade-icon mb-2">🚀</div>
                <h2 class="fw-bold text-primary">Upgrade Required</h2>
                <p class="text-muted">
                    Your current plan has reached its usage limit
                </p>
            </div>

            <!-- Status Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">

                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-1">Current Package</h6>
                            <span class="badge bg-label-primary fs-6">
                                <?= htmlspecialchars($sccode_current_package )  . $sccode_current_package_name ?>
                            </span>
                        </div>

                        <div class="col-md-6 text-md-end">
                            <strong>Usage:</strong>
                            <?= number_format($totalDurationPage / 60) ?> /
                            <?= number_format($view_limit) ?>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated
                             <?= $isExceeded ? 'bg-danger' : 'bg-success' ?>" style="width: <?= $usagePercent ?>%">
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <?= $upgradeMessage ?>
                    </div>

                </div>
            </div>

            <!-- Pricing -->
            <div class="row g-4">

                <!-- BASIC -->
                <div class="col-md-4">
                    <div class="card pricing-card">
                        <div class="card-body text-center">
                            <h5>Basic</h5>
                            <h3 class="text-primary">৳499</h3>
                            <small>/month</small>
                            <ul class="list-unstyled mt-3">
                                <li>✔ 1,000 usage</li>
                                <li>✔ Standard speed</li>
                                <li>✔ Email support</li>
                            </ul>
                            <button class="btn btn-outline-primary w-100" disabled>
                                Current Plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PRO -->
                <div class="col-md-4">
                    <div class="card pricing-card featured shadow-lg">
                        <div class="card-body text-center">
                            <span class="badge bg-primary mb-2">Most Popular</span>
                            <h5>Pro</h5>
                            <h3 class="text-primary">৳999</h3>
                            <small>/month</small>
                            <ul class="list-unstyled mt-3">
                                <li>✔ 10,000 usage</li>
                                <li>✔ Faster performance</li>
                                <li>✔ Priority support</li>
                            </ul>
                            <button class="btn btn-primary w-100 upgrade-btn" data-plan="pro">
                                Upgrade Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ENTERPRISE -->
                <div class="col-md-4">
                    <div class="card pricing-card">
                        <div class="card-body text-center">
                            <h5>Enterprise</h5>
                            <h3 class="text-primary">Custom</h3>
                            <small>&nbsp;</small>
                            <ul class="list-unstyled mt-3">
                                <li>✔ Unlimited usage</li>
                                <li>✔ Dedicated server</li>
                                <li>✔ 24/7 support</li>
                            </ul>
                            <button class="btn btn-outline-dark w-100" onclick="location.href='contact-support.php'">
                                Contact Sales
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirm Upgrade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>
                    You are about to upgrade to the
                    <strong id="selectedPlan"></strong> plan.
                </p>
                <p class="text-muted small">
                    Upgrade will be effective immediately.
                </p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="confirmUpgrade">
                    Proceed
                </button>
            </div>

        </div>
    </div>
</div>


<script>
    let chosenPlan = '';

    document.querySelectorAll('.upgrade-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            chosenPlan = btn.dataset.plan;
            document.getElementById('selectedPlan').innerText = chosenPlan.toUpperCase();
            new bootstrap.Modal(document.getElementById('upgradeModal')).show();
        });
    });

    document.getElementById('confirmUpgrade').addEventListener('click', () => {
        window.location.href = 'upgrade-process.php?plan=' + chosenPlan;
    });
</script>

<?php if ($isExceeded): ?>
    <script>
        console.warn('Usage limit exceeded – upgrade required');
    </script>
<?php endif; ?>