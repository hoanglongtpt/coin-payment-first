@if ($canSpin)
<div id="wheel-popup">
    <h2 style="color: white; text-align: center;">Don't miss your <br> free spin today!</h2>
    <div style="position: relative; width: 100%; max-width: 340px; aspect-ratio: 1;">
        <svg id="wheel-svg" width="340" height="340" viewBox="0 0 320 320"
            style="display: block; margin: 0 auto; transition: transform 5s cubic-bezier(0.25, 0.1, 0.25, 1); background: none;">
            <circle cx="160" cy="160" r="150" fill="#1762d1" stroke="#0047AB" stroke-width="14" />
            <circle cx="160" cy="160" r="140" fill="#153a7a" stroke="#153a7a" stroke-width="8" />
            <!-- Dots -->
            <g id="wheel-dots"></g>
            <!-- Wheel slices -->
            <g id="wheel-slices"></g>
            <circle cx="160" cy="160" r="28" fill="#1150b5" stroke="#1150b5" stroke-width="6" />
            <text x="160" y="168" text-anchor="middle" font-size="32" fill="#fff" font-weight="bold"
                font-family="Inter">★</text>
        </svg>
        <!-- Pointer -->
        <div style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); z-index:2;">
            <svg width="22" height="38">
                <circle cx="11" cy="10" r="6" fill="#ff4d4d" />
                <circle cx="11" cy="10" r="2.5" fill="#fff" />
                <polygon points="11,34 16,16 6,16" fill="#ff4d4d" />
            </svg>
        </div>
    </div>
    <button class="spin-btn" id="spin-btn" onclick="spinWheel()" id="spin-btn">Spin</button>
</div>
@endif