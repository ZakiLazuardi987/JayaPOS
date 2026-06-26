// ============================================================
//  JayaPOS — Bluetooth Thermal Printer (ESC/POS)
//  Target  : EPPOS EP-RPP02 / RPP02N  (58mm, BLE GATT)
//  Author  : Adapted for JayaPOS
// ============================================================

// ── Helper: format angka ke "1.000" ──────────────────────────
function fmt(angka) {
    return new Intl.NumberFormat("id-ID").format(angka);
}

// ── Helper: pad string kiri-kanan dalam lebar kolom ─────────
function padRow(left, right, totalWidth) {
    let spaces = totalWidth - left.length - right.length;
    if (spaces < 1) spaces = 1;
    return left + " ".repeat(spaces) + right;
}

// ── Konversi gambar ke buffer ESC/POS raster ─────────────────
async function getLogoBuffer(imgUrl, targetWidth) {
    return new Promise((resolve) => {
        let img = new Image();
        img.crossOrigin = "Anonymous";
        img.onload = function () {
            let finalWidth  = Math.floor(targetWidth / 8) * 8;
            let ratio       = finalWidth / img.width;
            let finalHeight = Math.floor((img.height * ratio) / 8) * 8;

            let canvas = document.createElement("canvas");
            canvas.width  = finalWidth;
            canvas.height = finalHeight;
            let ctx = canvas.getContext("2d", { willReadFrequently: true });

            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, finalWidth, finalHeight);
            ctx.drawImage(img, 0, 0, finalWidth, finalHeight);

            let imgData    = ctx.getImageData(0, 0, finalWidth, finalHeight).data;
            let widthBytes = finalWidth / 8;

            // GS v 0 0 xL xH yL yH
            let data = [
                0x1d, 0x76, 0x30, 0x00,
                widthBytes & 0xff, (widthBytes >> 8) & 0xff,
                finalHeight & 0xff, (finalHeight >> 8) & 0xff,
            ];

            for (let y = 0; y < finalHeight; y++) {
                for (let x = 0; x < finalWidth; x += 8) {
                    let byte = 0;
                    for (let b = 0; b < 8; b++) {
                        let i = (y * finalWidth + (x + b)) * 4;
                        let brightness = 0.299 * imgData[i] + 0.587 * imgData[i+1] + 0.114 * imgData[i+2];
                        if (imgData[i+3] > 128 && brightness < 128) byte |= 1 << (7 - b);
                    }
                    data.push(byte);
                }
            }
            resolve(data);
        };
        img.onerror = () => { console.warn("Gagal muat logo:", imgUrl); resolve([]); };
        img.src = imgUrl;
    });
}

// ── Manajemen koneksi persisten ───────────────────────────────
window.connectedBluetoothDevice = window.connectedBluetoothDevice || null;
window.connectedBluetoothServer = window.connectedBluetoothServer || null;
window.rememberedBluetoothDeviceId = localStorage.getItem("printerBluetoothDeviceId") || null;

function rememberBluetoothDevice(device) {
    if (!device) return;
    window.connectedBluetoothDevice = device;
    window.rememberedBluetoothDeviceId = device.id;
    localStorage.setItem("printerBluetoothDeviceId", device.id);
    device.removeEventListener("gattserverdisconnected", handleBluetoothDisconnected);
    device.addEventListener("gattserverdisconnected", handleBluetoothDisconnected);
}

function handleBluetoothDisconnected() {
    window.connectedBluetoothServer = null;
}

// Coba ambil device yang sudah pernah diberi izin saat halaman dimuat
window.bluetoothDevicesReady = (async () => {
    if (!navigator.bluetooth || !navigator.bluetooth.getDevices) return;
    try {
        const devices = await navigator.bluetooth.getDevices();
        if (devices.length === 0) return;
        const remembered = devices.find(d => d.id === window.rememberedBluetoothDeviceId);
        rememberBluetoothDevice(remembered || devices[devices.length - 1]);
    } catch (err) {
        console.warn("getDevices gagal:", err);
    }
})();

// ── Fungsi cetak utama ────────────────────────────────────────
// data = {
//   outletName, outletAddress, kasir,
//   orderId, tanggal, orderType,
//   items: [{ name, qty, unit_price, modifiers: [{name, price}] }],
//   subtotal, taxName, taxAmount,
//   scName, scAmount, discountAmount,
//   total, metode, nominal, kembali,
//   member
// }
window.printBluetoothReceipt = async function (data) {
    // ── 1. Cek dukungan browser ──
    if (!navigator.bluetooth) {
        throw new Error("Browser tidak mendukung Web Bluetooth. Gunakan Chrome via HTTPS.");
    }

    if (window.bluetoothDevicesReady) await window.bluetoothDevicesReady;

    // ── 2. Koneksi printer ──
    let device = window.connectedBluetoothDevice;
    let server = window.connectedBluetoothServer;

    if (!device) {
        device = await navigator.bluetooth.requestDevice({
            filters: [
                { namePrefix: "RPP" }
            ],
            optionalServices: [
                "000018f0-0000-1000-8000-00805f9b34fb",
                "e7810a71-73ae-499d-8c15-faa9aef0c3f2",
            ],
        });
        rememberBluetoothDevice(device);
    }

    if (!server || !server.connected) {
        try {
            server = await device.gatt.connect();
            window.connectedBluetoothServer = server;
        } catch (err) {
            window.connectedBluetoothServer = null;
            rememberBluetoothDevice(device);
            throw new Error("Gagal konek ke printer. Pastikan printer menyala, lalu coba lagi.");
        }
    }

    rememberBluetoothDevice(device);
    window.connectedBluetoothServer = server;

    // ── 3. Ambil karakteristik BLE ──
    let service;
    try {
        service = await server.getPrimaryService("000018f0-0000-1000-8000-00805f9b34fb");
    } catch {
        service = await server.getPrimaryService("e7810a71-73ae-499d-8c15-faa9aef0c3f2");
    }

    const characteristics = await service.getCharacteristics();
    const characteristic = characteristics.find(
        c => c.properties.write || c.properties.writeWithoutResponse
    );
    if (!characteristic) throw new Error("Printer tidak dapat menerima data.");

    // ── 4. Susun buffer ESC/POS ──
    const ESC = 0x1b, GS = 0x1d;
    let buf = [];
    const enc = new TextEncoder();
    const W = 32; // Lebar karakter untuk kertas 58mm

    // Init
    buf.push(ESC, 0x40);

    // Logo (tengah)
    buf.push(ESC, 0x61, 0x01); // center
    try {
        const logoBuf = await getLogoBuffer("/assets/jaya_text.png", 200);
        if (logoBuf && logoBuf.length > 0) {
            buf.push(...logoBuf);
            buf.push(0x0a);
        }
    } catch (e) { console.warn("Lewati logo:", e); }

    // Nama outlet (bold, normal size)
    buf.push(ESC, 0x45, 0x01); // bold on
    buf.push(...enc.encode((data.outletName || "Toko Kopi Jaya") + "\n"));
    buf.push(ESC, 0x45, 0x00); // bold off

    // Alamat & Telp
    buf.push(ESC, 0x61, 0x01); // center
    const addr = data.outletAddress || "";
    if (addr.length <= W) {
        buf.push(...enc.encode(addr + "\n"));
    } else {
        buf.push(...enc.encode(addr.substring(0, W) + "\n"));
        buf.push(...enc.encode(addr.substring(W) + "\n"));
    }
    if (data.outletPhone) {
        buf.push(...enc.encode("Telp: " + data.outletPhone + "\n"));
    }

    buf.push(...enc.encode("-".repeat(W) + "\n"));

    // Info transaksi (left align)
    buf.push(ESC, 0x61, 0x00); // left
    buf.push(...enc.encode("Nota    : #" + data.orderId + "\n"));
    buf.push(...enc.encode("Tanggal : " + data.tanggal + "\n"));
    buf.push(...enc.encode("Kasir   : " + (data.kasir || "-") + "\n"));
    if (data.pickupCode) {
        buf.push(...enc.encode("Kode Psnn: " + data.pickupCode + "\n"));
    }
    if (data.member) {
        buf.push(...enc.encode("Pelanggan: " + data.member + "\n"));
    }
    buf.push(...enc.encode("-".repeat(W) + "\n"));

    // Items
    data.items.forEach(item => {
        let name = item.name.trim();
        if (name.length > W) name = name.substring(0, W - 1);
        buf.push(ESC, 0x45, 0x01); // bold
        buf.push(...enc.encode(name + "\n"));
        buf.push(ESC, 0x45, 0x00); // bold off

        const basePrice = item.unit_price;
        const totalItem = item.qty * basePrice;
        const qtyStr   = item.qty + "x  Rp " + fmt(basePrice);
        buf.push(...enc.encode(padRow(qtyStr, "Rp " + fmt(totalItem), W) + "\n"));

        // Modifier
        if (item.modifiers && item.modifiers.length > 0) {
            item.modifiers.forEach(mod => {
                const modLine = "  + " + mod.name;
                const modPrice = mod.price > 0 ? "+" + fmt(mod.price) : "gratis";
                buf.push(...enc.encode(padRow(modLine, modPrice, W) + "\n"));
            });
        }
    });

    // Ringkasan harga
    buf.push(...enc.encode("-".repeat(W) + "\n"));
    buf.push(...enc.encode(padRow("Subtotal", "Rp " + fmt(data.subtotal), W) + "\n"));

    if (data.discountAmount && data.discountAmount > 0) {
        buf.push(...enc.encode(padRow("Diskon", "-Rp " + fmt(data.discountAmount), W) + "\n"));
    }
    if (data.scAmount && data.scAmount > 0) {
        buf.push(...enc.encode(padRow(data.scName || "Service Charge", "Rp " + fmt(data.scAmount), W) + "\n"));
    }
    if (data.taxAmount && data.taxAmount > 0) {
        buf.push(...enc.encode(padRow(data.taxName || "Pajak", "Rp " + fmt(data.taxAmount), W) + "\n"));
    }

    buf.push(...enc.encode("-".repeat(W) + "\n"));

    // Total (bold, besar)
    buf.push(ESC, 0x45, 0x01);
    buf.push(...enc.encode(padRow("Total", "Rp " + fmt(data.total), W) + "\n"));
    buf.push(ESC, 0x45, 0x00);
    
    // Informasi Pembayaran (Hanya jika LUNAS)
    if (data.status === 'paid') {
        if (data.metode && (data.metode.toLowerCase() === 'cash' || data.metode.toLowerCase() === 'tunai')) {
            buf.push(...enc.encode(padRow("Tunai", "Rp " + fmt(data.nominal), W) + "\n"));
            buf.push(...enc.encode(padRow("Kembalian", "Rp " + fmt(data.kembali), W) + "\n"));
        } else {
            buf.push(...enc.encode(padRow("Metode Bayar", (data.metode || "").toUpperCase(), W) + "\n"));
        }
    }

    // Footer
    buf.push(...enc.encode("-".repeat(W) + "\n"));
    buf.push(ESC, 0x61, 0x01); // center
    
    buf.push(...enc.encode("Pesanan: " + (data.orderType || "DINE-IN").toUpperCase() + "\n"));
    
    buf.push(ESC, 0x45, 0x01); // bold
    if (data.status === 'paid') {
        buf.push(...enc.encode("LUNAS\n\n"));
    } else {
        buf.push(...enc.encode("BELUM BAYAR (SEMENTARA)\n\n"));
    }
    buf.push(ESC, 0x45, 0x00); // normal

    buf.push(...enc.encode("Terima Kasih\n"));
    buf.push(...enc.encode("Atas Kunjungan Anda\n"));
    buf.push(...enc.encode("\n\n\n")); // Feed kertas keluar

    // ── 5. Kirim ke printer (chunk 20 byte) ──
    const payload   = new Uint8Array(buf);
    const chunkSize = 20;
    for (let i = 0; i < payload.length; i += chunkSize) {
        const chunk = payload.slice(i, i + chunkSize);
        if (characteristic.properties.writeWithoutResponse) {
            await characteristic.writeValueWithoutResponse(chunk);
            await new Promise(r => setTimeout(r, 10)); // jeda agar BLE tidak overflow
        } else {
            await characteristic.writeValue(chunk);
        }
    }

    return true;
};
