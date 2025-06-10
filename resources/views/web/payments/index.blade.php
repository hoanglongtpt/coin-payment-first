@extends('web.layouts.master_layout')
@section('content')
    <div class="container">
        @include('web.includes.header')

        <div class="section-header">
            <div>
                <div class="title">Select the package you want to buy</div>
                <div class="subtitle">Packages with the symbol are promotional packages for first top-up, only
                    applicable once per month</div>
            </div>
            <div class="promo">Promotion +50🍀</div>
        </div>

        <div class="paypal-btn"><img width="12" height="12" src="assets/images/icon_paypal.png" alt="icon_paypal">
            Paypal</div>

        <div class="grid">
            <div class="card" id="card-5usd">
                <div class="amount">5 USD</div>
                <div class="reward">19 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">10 USD</div>
                <div class="reward">47 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">15 USD</div>
                <div class="reward">83 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">20 USD</div>
                <div class="reward">128 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">30 USD</div>
                <div class="reward">239 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">50 USD</div>
                <div class="reward">485 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">100 USD</div>
                <div class="reward">1160 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card" id="card-5usd">
                <div class="amount">150 USD</div>
                <div class="reward">1913 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
            <div class="card full">
                <div class="amount">199 USD</div>
                <div class="reward">2982 🎟️ + <span class="bonus">10🍀</span></div>
            </div>
        </div>

        <div class="vip">
            🚀 <span class="vip-text">Buy VIP Day</span>
            <span class="vip-new">New</span>
        </div>
    </div>

    <!-- FULL UPDATED WHEEL SECTION WITH SVG + POPUP -->
    @include('web.includes.wheel_popup')

    <div id="result-popup"
        style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#1e1e1e; padding:30px; border-radius:16px; text-align:center; z-index:1001; color:white; font-family:'Inter',sans-serif;">
        <h3 style="margin-top:0">You received</h3>
        <div id="reward-result" style="font-size:36px; margin:10px 0;">+50🍀</div>
        <p>Bonus credits after payment.<br>The credits have been added to your account.</p>
        <button onclick="closeResultPopup()"
            style="margin-top:15px; padding:10px 20px; background:linear-gradient(to right,#f5c242,#e9a60c); color:#FFFFFF; border:none; border-radius:20px; font-weight:bold; cursor:pointer;">Go
            to checkout page</button>
    </div>

    <div id="vip-modal" style="display:none;">
        <div class="vip-modal-overlay"></div>
        <div class="vip-modal-content">
            <div class="vip-modal-title">
                This is a daily package, Every day you will receive the corresponding amount of credit at 00:00 UTC, for
                example you buy a package of 1195 with 999 🎟️ every day
            </div>
            <div class="vip-modal-desc">
                Every 00:00 UTC you will be refreshed your vip balance to 999 🎟️ maintained for 30 days, the last day
                will not be deleted when expired
            </div>
            <div class="vip-modal-list">
                <div class="vip-modal-item" id="vip-card-usd">
                    <div class="vip-modal-item-left">
                        <div class="vip-modal-item-amount">19 USD</div>
                        <div class="vip-modal-item-ticket">
                            29 <span class="vip-ticket-icon">🎟️</span>
                        </div>
                    </div>
                    <div class="vip-modal-item-right">
                        Daily for 142 Days 7
                    </div>
                </div>
                <div class="vip-modal-item" id="vip-card-usd">
                    <div class="vip-modal-item-left">
                        <div class="vip-modal-item-amount">49 USD</div>
                        <div class="vip-modal-item-ticket">
                            49 <span class="vip-ticket-icon">🎟️</span>
                        </div>
                    </div>
                    <div class="vip-modal-item-right">
                        Daily for 272 Days 7
                    </div>
                </div>
                <div class="vip-modal-item" id="vip-card-usd">
                    <div class="vip-modal-item-left">
                        <div class="vip-modal-item-amount">69 USD</div>
                        <div class="vip-modal-item-ticket">
                            69 <span class="vip-ticket-icon">🎟️</span>
                        </div>
                    </div>
                    <div class="vip-modal-item-right">
                        Daily for 143 Days 7
                    </div>
                </div>
                <div class="vip-modal-item" id="vip-card-usd">
                    <div class="vip-modal-item-left">
                        <div class="vip-modal-item-amount">119 USD</div>
                        <div class="vip-modal-item-ticket">
                            119 <span class="vip-ticket-icon">🎟️</span>
                        </div>
                    </div>
                    <div class="vip-modal-item-right">
                        Daily for 273 Days 7
                    </div>
                </div>
            </div>
            <button class="vip-modal-btn"><span style="font-size:18px;">🎟️</span> Buy VIP</button>
        </div>
    </div>

    <!-- Modal for order details -->
    <div id="order-modal" style="display:none;">
        <div class="order-modal-overlay"></div>
        <div class="order-modal-content">
            <div class="order-modal-title">Order details</div>
            <div class="order-modal-price" id="order-modal-price">$5.00</div>
            <div class="order-modal-table">
                <div class="order-modal-row">
                    <div><span id="order-modal-reward">19 <span class="order-ticket">🎟️</span> + 10<span
                                class="order-clover">🍀</span></span>
                    </div>
                    <div>$5.00</div>
                </div>
                <div class="order-modal-row">
                    <div>Promotion</div>
                    <div>+50<span class="order-clover">🍀</span></div>
                </div>
                <div class="order-modal-row">
                    <div>Subtotal</div>
                    <div>$5.00</div>
                </div>
                <div class="order-modal-row">
                    <div>Tax</div>
                    <div>$0.00</div>
                </div>
                <div class="order-modal-row total">
                    <div>Total due today</div>
                    <div>$5.00</div>
                </div>
            </div>
            <button class="order-modal-btn">Connect wallet</button>
            <div class="order-modal-note">
                By confirming your subscription, you allow us to charge you for future payments in accordance with their
                terms. You can always cancel your subscription.<br><br>
                All sales are charged in USD and all sales are final. You will be charged $5.00 USD immediately.
            </div>
            <div class="order-modal-links">
                <a href="#">Terms</a> | <a href="#">Privacy</a> | <a href="#">Payment policy</a> | <a
                    href="#">Contact</a>
            </div>
        </div>
    </div>
@endsection
