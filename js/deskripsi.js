// script.js
const text = `Website toko buku ini menyediakan layanan lengkap untuk memudahkan pengguna dalam mencari dan membeli buku secara online. 
Fitur utamanya meliputi Daftar Buku dengan katalog yang dapat difilter berdasarkan kategori, penulis, atau harga, serta Keranjang Belanja untuk mengelola pesanan. 
Pengguna juga dapat melihat status pesanan di menu Transaksi Saya, dengan riwayat pembelian yang tercatat.

Untuk manajemen bisnis, admin dapat mengakses Laporan Stok Buku untuk memantau ketersediaan barang dan Laporan Transaksi guna melihat penjualan secara periodik. 
Website ini juga dilengkapi dengan Beranda yang menampilkan promosi dan rekomendasi buku, serta Kontak dan Login/Daftar bagi pengguna baru atau yang sudah terdaftar.`;

let index = 0;
const typingSpeed = 20; // Faster typing effect

function typeText() {
  if (index < text.length) {
    document.getElementById("typing-text").innerHTML += text.charAt(index);
    index++;
    setTimeout(typeText, typingSpeed);
  }
}

window.onload = typeText;
