// Простая обертка над IndexedDB для хранения регистраций
class RegistrationDB {
    constructor() {
        this.dbName = 'WaifuWarfareDB';
        this.storeName = 'registrations';
        this.version = 1;
    }

    // Открыть/создать базу данных
    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.version);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(this.storeName)) {
                    const store = db.createObjectStore(this.storeName, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('email', 'email', { unique: false });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                }
            };
        });
    }

    // Добавить регистрацию
    async addRegistration(registration) {
        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.add({
                ...registration,
                timestamp: new Date().toISOString(),
                id: Date.now()
            });

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Получить все регистрации
    async getAllRegistrations() {
        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.storeName], 'readonly');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Экспорт в текстовый файл
    async exportToFile() {
        const registrations = await this.getAllRegistrations();
        let content = "=== СПИСОК РЕГИСТРАЦИЙ ===\n\n";

        registrations.forEach((reg, index) => {
            content += `Регистрация #${index + 1}\n`;
            content += `Дата: ${new Date(reg.timestamp).toLocaleString('ru-RU')}\n`;
            content += `Email: ${reg.email}\n`;
            content += `Имя: ${reg.username}\n`;
            content += `Платформа: ${reg.platform}\n`;
            content += `Регион: ${reg.region}\n\n`;
        });

        return content;
    }

    // Очистить базу данных
    async clearAll() {
        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }
}
