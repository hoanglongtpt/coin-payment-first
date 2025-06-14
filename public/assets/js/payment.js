let rotation = 0;
const rewardTexts = ["200", "150", "100", "50", "25", "10", "5", "5"];
const colors = [
    "#fff",      // 200
    "#ff4d4d",   // 150
    "#ffd166",   // 100
    "#b3d4fc",   // 50
    "#fff",      // 25
    "#ff4d4d",   // 10
    "#ffd166",   // 5
    "#b3d4fc"    // 5
];
const textColors = [
    "#222", "#fff", "#222", "#222", "#222", "#fff", "#222", "#222"
];
const dotColors = [
    "#ff4d4d", "#ffd166", "#ff4d4d", "#ffd166", "#ff4d4d", "#ffd166", "#ff4d4d", "#ffd166"
];

function drawWheel() {
    const slices = document.getElementById("wheel-slices");
    if (!slices) {
        // console.error("Element #wheel-slices not found.");
        return; // Không thực hiện vẽ nếu không tìm thấy phần tử
    }
    slices.innerHTML = "";
    const cx = 160, cy = 160;
    const rOuter = 152;
    const rInner = 28;
    const boTron = 18;
    const n = rewardTexts.length; // <-- THÊM DÒNG NÀY

    for (let i = 0; i < n; i++) {
        const startAngle = (i / n) * 2 * Math.PI - Math.PI / 2;
        const endAngle = ((i + 1) / n) * 2 * Math.PI - Math.PI / 2;
        const midAngle = (startAngle + endAngle) / 2; // Thay đổi ở đây

        // Điểm ngoài cùng (bo tròn)
        const x1 = cx + rOuter * Math.cos(startAngle);
        const y1 = cy + rOuter * Math.sin(startAngle);
        const x2 = cx + rOuter * Math.cos(endAngle);
        const y2 = cy + rOuter * Math.sin(endAngle);

        // Điểm bo tròn (lùi vào trong)
        const x1r = cx + (rOuter - boTron) * Math.cos(startAngle);
        const y1r = cy + (rOuter - boTron) * Math.sin(startAngle);
        const x2r = cx + (rOuter - boTron) * Math.cos(endAngle);
        const y2r = cy + (rOuter - boTron) * Math.sin(endAngle);

        // Điểm trong cùng (tâm)
        const x1i = cx + rInner * Math.cos(endAngle);
        const y1i = cy + rInner * Math.sin(endAngle);
        const x2i = cx + rInner * Math.cos(startAngle);
        const y2i = cy + rInner * Math.sin(startAngle);

        const largeArc = endAngle - startAngle > Math.PI ? 1 : 0;
        const path = [
            `M ${x2i} ${y2i}`,
            `L ${x1r} ${y1r}`,
            // Bo tròn mép ngoài đầu 1
            `Q ${x1} ${y1} ${(x1 + x2) / 2} ${(y1 + y2) / 2}`,
            // Bo tròn mép ngoài đầu 2
            `Q ${x2} ${y2} ${x2r} ${y2r}`,
            `L ${x1i} ${y1i}`,
            `A ${rInner} ${rInner} 0 ${largeArc} 0 ${x2i} ${y2i}`,
            "Z"
        ].join(" ");

        const slice = document.createElementNS("http://www.w3.org/2000/svg", "path");
        slice.setAttribute("d", path);
        slice.setAttribute("fill", colors[i]);
        slice.setAttribute("stroke", "#153a7a");
        slice.setAttribute("stroke-width", "4");
        slice.setAttribute("filter", "drop-shadow(0 2px 2px #0002)");
        slices.appendChild(slice);

        // Draw reward text (giữ nguyên)
        const tx = cx + ((rOuter + rInner) / 2) * Math.cos(midAngle);
        const ty = cy + ((rOuter + rInner) / 2) * Math.sin(midAngle) + 2;
        const angleDeg = (midAngle * 180) / Math.PI; // đổi sang độ

        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute("x", tx);
        text.setAttribute("y", ty);
        text.setAttribute("text-anchor", "middle");
        text.setAttribute("font-size", "18");
        text.setAttribute("font-family", "Inter");
        text.setAttribute("font-weight", "bold");
        text.setAttribute("fill", textColors[i]);
        text.setAttribute("dominant-baseline", "middle");
        // Thêm dòng này để xoay text theo lát bánh
        text.setAttribute("transform", `rotate(${angleDeg} ${tx} ${ty})`);
        text.textContent = rewardTexts[i];
        slices.appendChild(text);

        // Emoji cũng xoay theo lát bánh
        const emoji = document.createElementNS("http://www.w3.org/2000/svg", "text");
        emoji.setAttribute("x", tx + 32 * Math.cos(midAngle));
        emoji.setAttribute("y", ty + 22 * Math.sin(midAngle));
        emoji.setAttribute("text-anchor", "middle");
        emoji.setAttribute("font-size", "15");
        emoji.setAttribute("font-family", "Segoe UI Emoji, Inter");
        emoji.setAttribute("dominant-baseline", "middle");
        emoji.setAttribute("transform", `rotate(${angleDeg} ${tx + 32 * Math.cos(midAngle)} ${ty + 22 * Math.sin(midAngle)})`);
        emoji.textContent = "🍀";
        slices.appendChild(emoji);
    }

    // Draw border dots (nằm ở viền đầu tiên, nhỏ lại)
    const dots = document.getElementById("wheel-dots");
    dots.innerHTML = "";
    const dotRadius = 154;
    for (let i = 0; i < n; i++) {
        const angle = (i / n) * 2 * Math.PI - Math.PI / 2;
        const dx = cx + dotRadius * Math.cos(angle);
        const dy = cy + dotRadius * Math.sin(angle);
        const dot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        dot.setAttribute("cx", dx);
        dot.setAttribute("cy", dy);
        dot.setAttribute("r", "4");
        dot.setAttribute("fill", dotColors[i]);
        dot.setAttribute("stroke", "#fff");
        dot.setAttribute("stroke-width", "2");
        dots.appendChild(dot);
    }
}
drawWheel();


function spinWheel() {
    const wheel = document.getElementById("wheel-svg");
    const btn = document.getElementById("spin-btn");
    btn.disabled = true;
    btn.innerText = "Spinning...";
    // const index = Math.floor(Math.random() * rewardTexts.length);
    const index = 3;
    const sliceAngle = 360 / rewardTexts.length;
    const targetAngle = 360 * 5 + (360 - index * sliceAngle - sliceAngle / 2);
    rotation += targetAngle;
    wheel.style.transform = `rotate(${rotation}deg)`;
    setTimeout(() => {
        document.getElementById("reward-result").innerText = "+" + rewardTexts[index] + "🍀";
        // document.getElementById("result-popup").style.display = "block";
        openResultPopup();
        document.getElementById("wheel-popup").style.display = "none";
        document.getElementById("wheel-popup").classList.remove("show");
        updateWheelStatus(index);
        btn.disabled = false;
        btn.innerText = "Spin";
    }, 5200);
}
function closeResultPopup() {
    document.getElementById("result-popup").style.display = "none";
    location.reload();

}
window.onload = () => {
      document.getElementById("wheel-popup").classList.add("show");
};
document.querySelector('.vip').onclick = function () {
    document.getElementById('vip-modal').style.display = 'flex';
};
document.querySelector('#vip-modal').onclick = function (e) {
    if (e.target.classList.contains('vip-modal-overlay')) {
        document.getElementById('vip-modal').style.display = 'none';
    }
};

document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', function() {
        const amount = card.querySelector('.amount').innerText;
        const price = card.querySelector('.price').innerText;
        const reward = card.querySelector('.reward').innerText;
        const package_sku = card.querySelector('.package_sku').innerText;
        const member_id = card.querySelector('.member_id').innerText;
        const tokens_first_time = card.querySelector('.tokens_first_time').innerText;
        const promotion = card.querySelector('.promotion').innerText;
        const sale = card.querySelector('.sale').innerText;

        // Cập nhật giá trị trong modal
        document.getElementById('order-modal-price').innerText = amount;
        document.getElementById('order-modal-reward').innerHTML = reward;
        document.getElementById('order-modal-total').innerHTML = amount;
        document.getElementById('order-modal-subtotal').innerHTML = amount;
        document.getElementById('order-modal-total-today').innerHTML = amount;

        const checkoutButton = document.getElementById('checkout-button'); // ID của button trong modal
        if (checkoutButton) {
            checkoutButton.setAttribute('data-url', `/paypal/checkout?member_id=${member_id}&package_sku=${package_sku}&tokens_first_time=${tokens_first_time}&tokens_first_time=${tokens_first_time}&promotion=${promotion}&sale=${sale}&price=${price}`);
        }

        // Hiển thị modal
        document.getElementById('order-modal').style.display = 'flex';
    });
});



// Đóng modal khi người dùng nhấp vào overlay
document.querySelector('#order-modal').addEventListener('click', function(e) {
    if (e.target.classList.contains('order-modal-overlay')) {
        document.getElementById('order-modal').style.display = 'none';
    }
});


document.querySelectorAll('.vip-modal-item').forEach(item => {
    item.addEventListener('click', function() {
        document.getElementById('order-modal').style.display = 'flex';
    });
});

document.querySelectorAll('.vip-modal-item').forEach(card => {
    card.addEventListener('click', function() {
        const amount = card.querySelector('.vip-modal-item-amount').innerText;
        const coin = card.querySelector('.vip-modal-item-ticket').innerText;
        const vip_card_id = card.querySelector('.vip_card_id').innerText;
        const member_id = card.querySelector('.member_id').innerText;

        const price = card.querySelector('.price').innerText;
        // const reward = card.querySelector('.reward').innerText;
        const package_sku = card.querySelector('.package_sku').innerText;
        const tokens_first_time = card.querySelector('.tokens_first_time').innerText;
        const promotion = card.querySelector('.promotion').innerText;
        const sale = card.querySelector('.sale').innerText;

        // Cập nhật giá trị trong modal
        document.getElementById('order-modal-price').innerText = amount;
        document.getElementById('order-modal-reward').innerHTML = coin;
        document.getElementById('order-modal-total').innerHTML = amount;
        document.getElementById('order-modal-subtotal').innerHTML = amount;
        document.getElementById('order-modal-total-today').innerHTML = amount;

        const checkoutButton = document.getElementById('checkout-button'); // ID của button trong modal
        if (checkoutButton) {
            checkoutButton.setAttribute('data-url', `/paypal/checkout-vip?member_id=${member_id}&package_sku=${package_sku}&tokens_first_time=${tokens_first_time}&tokens_first_time=${tokens_first_time}&promotion=${promotion}&sale=${sale}&price=${price}`);
        }

        // Hiển thị modal
        document.getElementById('order-modal').style.display = 'flex';
    });
});

// Hàm xử lý khi click vào button checkout trong modal (nếu cần)
document.getElementById('checkout-button').addEventListener('click', function () {
    const button = this;
    const url = button.getAttribute('data-url');

    if (url) {
        // Hiển thị loading
        document.getElementById('loading-spinner').style.display = 'block';

        // Disable nút để tránh bấm nhiều lần
        button.disabled = true;
        button.innerText = 'Processing...';

        // Chuyển trang sau một chút để loading hiển thị rõ
        setTimeout(() => {
            window.location.href = url;
        }, 300);
    }
});

