# 💪 Strategic Nutrition V1 (Programim) — Hyper-Speed AI & Arena

![License](https://img.shields.io/badge/License-MIT-blue.svg) 
![Backend](https://img.shields.io/badge/Backend-PHP%208.x-777bb4.svg) 
![Frontend](https://img.shields.io/badge/Frontend-VanillaJS%20%7C%20TailwindCSS-38b2ac.svg) 
![AI Engine](https://img.shields.io/badge/AI-Cerebras%20Llama%203.1-red.svg) 

Strategic Nutrition V1, modern fitness dünyası için geliştirilmiş, **Cerebras Llama 3.1 8B** modelinin gücünü kullanan, saniyeler içinde kişiye özel beslenme planları üreten ve sporcuları bir araya getiren hibrit bir platformdur. Sadece bir kalori hesaplayıcı değil, aynı zamanda rekabetçi bir topluluk merkezidir.

---

## 🎨 Öne Çıkan Modüller

### 🧠 AI Beslenme Koçu (Hyper-Speed AI)
Cerebras'ın inanılmaz düşük gecikmeli (low-latency) altyapısını kullanarak, bütçenize, fiziksel durumunuza ve hedeflerinize (Güray Training Protocol uyumlu) göre 7 günlük beslenme planlarını anlık olarak oluşturur. 
*   **Akıllı Makro Dengesi**: Anabolik/Naturel seçiminize göre protein ve yağ dengesini AI otomatik ayarlar.
*   **Bütçe Segmentleri**: Öğrenci menülerinden "zengin" menülere kadar esnek seçenekler.

### ⚡ Elite Arena (PvP Quiz)
Hız ve bilginin birleştiği yer! Diğer kullanıcılara karşı spor bilginizi yarıştırın.
*   **Gerçek Zamanlı HUD**: Rakibinizin hangi soruda olduğunu ve skorunu canlı olarak takip edin.
*   **Dinamik Sorular**: AI tarafından anlık üretilen 10 soruluk testler.

### 🤝 Topluluk & Sosyal (Live Chat)
Sporcular yalnız antrenman yapmasın diye geliştirdiğimiz sosyal panel.
*   **Anlık Mesajlaşma**: Diğer üyelerle sohbet edin, fotoğraf paylaşın.
*   **Online/Offline Takibi**: Kimlerin online olduğunu görün, Arena'ya meydan okuyun.

---

## 📸 Ekran Görüntüleri
> [!TIP]
> Buraya projenin dashboard, arena ve beslenme planı ekranlarından aldığın görselleri eklemeyi unutma!

---

## 🛠️ Kurulum

1.  Bu repoyu bilgisayarınıza klonlayın:
    ```bash
    git clone https://github.com/akin-34/Hypertrophy-Max.git
    ```
2.  `htdocs/config.sample.php` dosyasının adını `config.php` olarak değiştirin.
3.  Veritabanı bilgilerinizi ve [Cerebras Cloud](https://cloud.cerebras.ai/) API anahtarınızı `config.php` içine girin.
4.  Bir Web Sunucusu (Apache-PHP) üzerinde çalıştırın.

---

## 💻 Under The Hood (Kullanılan Kodlar)

Bu proje, modern web teknolojilerini bir araya getirerek hem performanslı hem de görsel olarak tatmin edici bir deneyim sunar:

*   **Logic**: PHP 8.x (Backend Proxying & DB Management)
*   **UI Framework**: TailwindCSS (Modern Glassmorphism Design)
*   **AI Engine**: Cerebras Inference (Llama 3.1 8B Instruct)
*   **Client**: Async Fetch API & Vanilla JS
*   **PDF**: HTML2PDF.js (Müşteri için anlık plan dökümü)

---

> [!NOTE]
> Bu proje, sporcuların gelişimini hızlandırmak için samimi bir hobi projesi olarak geliştirilmiştir. **Progressive Overload** sadece antrenmanda değil, kodun kalitesinde de geçerlidir! 💪🔥
