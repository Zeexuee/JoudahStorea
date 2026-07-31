import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report

# 1. Membaca Dataset
df = pd.read_csv("Document from zeee.csv")
# 2. Pra-pemrosesan Data Dasar
# Mengisi nilai kosong dengan median dan mengubah data teks menjadi angka (jika ada)
df = df.fillna(df.median(numeric_only=True))
df = pd.get_dummies(df, drop_first=True)
# 3. Seleksi Fitur Berbasis Threshold Korelasi
# Menghitung korelasi setiap kolom terhadap target 'Tingkat_Kemiskinan'
korelasi = df.corr()['Tingkat_Kemiskinan'].abs()
# Menentukan ambang batas (threshold), misalnya 0.05
# Kita mengambil semua variabel yang memiliki nilai korelasi di atas batas ini
nilai_threshold = 0.05
kolom_terpilih = korelasi[korelasi > nilai_threshold].index.tolist()

# Pastikan kolom target tidak ikut masuk ke dalam daftar fitur (X)
if 'Tingkat_Kemiskinan' in kolom_terpilih:
    kolom_terpilih.remove('Tingkat_Kemiskinan')

print(f"Jumlah fitur yang digunakan setelah melewati threshold: {len(kolom_terpilih)}\n")
# Memisahkan Fitur (X) yang sudah diseleksi dan Target (y)
X = df[kolom_terpilih]
y = df['Tingkat_Kemiskinan']
# 4. Membagi Data Latih dan Data Uji (80% Train, 20% Test)
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
# 5. Membuat dan Melatih Model Random Forest
# n_estimators = 100 berarti kita akan membuat 100 pohon keputusan (Decision Tree) di dalam hutan ini
# max_depth = 5 membatasi kedalaman pohon untuk mencegah overfitting
model_rf = RandomForestClassifier(n_estimators=100, max_depth=5, random_state=42)
# Melatih model
model_rf.fit(X_train, y_train)
# 6. Melakukan Prediksi
y_pred = model_rf.predict(X_test)
# 7. Evaluasi Model
akurasi = accuracy_score(y_test, y_pred)
print(f"Akurasi Model Random Forest: {akurasi * 100:.2f}%\n")
print("Laporan Klasifikasi Detail:")
print(classification_report(y_test, y_pred))