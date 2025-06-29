# Service Azure Blob Storage - Upload Direct depuis le Front-End

## 🚀 Vue d'ensemble

Ce service permet au front-end d'uploader directement des images vers Azure Blob Storage sans passer par le serveur Symfony, tout en gardant le contrôle côté backend.

## 🔄 Flux d'utilisation

1. **Front-end** demande une URL présignée au backend
2. **Backend** génère l'URL avec token SAS et retourne les paramètres
3. **Front-end** uploade directement vers Azure avec l'URL fournie
4. **Front-end** confirme l'upload au backend pour obtenir l'URL finale

## 📋 Configuration requise

### Variables d'environnement (.env)
```bash
AZURE_STORAGE_ACCOUNT=your_storage_account_name
AZURE_STORAGE_KEY=your_storage_account_key  
AZURE_STORAGE_CONTAINER=images
```

### Permissions Azure
Le container doit autoriser l'accès public en lecture pour les images publiques, ou rester privé si vous voulez des URLs présignées.

## 🔌 API Endpoints

### 1. Générer URL d'upload
```http
POST /api/images/upload-url
Content-Type: application/json

{
    "filename": "photo.jpg",
    "expiry": 3600
}
```

**Réponse :**
```json
{
    "success": true,
    "data": {
        "upload_url": "https://account.blob.core.windows.net/images/2025/06/29/img_abc123.jpg?sv=2020-04-08&sr=b&sp=w&st=...",
        "blob_name": "2025/06/29/img_abc123.jpg",
        "expires_at": "2025-06-29T15:30:00Z",
        "headers": {
            "x-ms-blob-type": "BlockBlob",
            "x-ms-blob-content-type": "image/jpeg"
        }
    },
    "instructions": {
        "method": "PUT",
        "headers": { ... },
        "note": "Envoyez le fichier en tant que body de la requête PUT"
    }
}
```

### 2. Vérifier l'upload
```http
POST /api/images/verify
Content-Type: application/json

{
    "blob_name": "2025/06/29/img_abc123.jpg"
}
```

### 3. Obtenir URL publique
```http
GET /api/images/url/2025%2F06%2F29%2Fimg_abc123.jpg
```

### 4. Supprimer une image
```http
DELETE /api/images/2025%2F06%2F29%2Fimg_abc123.jpg
```

## 💻 Exemples côté Front-End

### JavaScript/TypeScript
```typescript
class AzureImageUploader {
    private apiBaseUrl = '/api/images';

    async uploadImage(file: File): Promise<string> {
        try {
            // 1. Demander l'URL d'upload
            const uploadData = await this.getUploadUrl(file.name);
            
            // 2. Uploader directement vers Azure
            await this.uploadToAzure(file, uploadData);
            
            // 3. Vérifier l'upload
            const result = await this.verifyUpload(uploadData.blob_name);
            
            return result.image_url;
        } catch (error) {
            console.error('Upload failed:', error);
            throw error;
        }
    }

    private async getUploadUrl(filename: string) {
        const response = await fetch(`${this.apiBaseUrl}/upload-url`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename })
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error);
        
        return data.data;
    }

    private async uploadToAzure(file: File, uploadData: any) {
        const response = await fetch(uploadData.upload_url, {
            method: 'PUT',
            headers: uploadData.headers,
            body: file
        });
        
        if (!response.ok) {
            throw new Error(`Azure upload failed: ${response.status}`);
        }
    }

    private async verifyUpload(blobName: string) {
        const response = await fetch(`${this.apiBaseUrl}/verify`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ blob_name: blobName })
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error);
        
        return data;
    }
}

// Utilisation
const uploader = new AzureImageUploader();
const imageUrl = await uploader.uploadImage(fileFromInput);
console.log('Image uploadée:', imageUrl);
```

### Vue.js Composable
```typescript
import { ref } from 'vue';

export const useAzureImageUpload = () => {
    const uploading = ref(false);
    const error = ref<string | null>(null);

    const uploadImage = async (file: File): Promise<string | null> => {
        uploading.value = true;
        error.value = null;

        try {
            // Même logique que React...
            // ... code d'upload ...
            
            return imageUrl;
        } catch (err) {
            error.value = err.message;
            return null;
        } finally {
            uploading.value = false;
        }
    };

    return { uploadImage, uploading, error };
};
```

## 🔒 Sécurité

### Avantages de cette approche
- ✅ **Pas de transit serveur** : Images uploadées directement vers Azure
- ✅ **URLs temporaires** : Les URLs d'upload expirent (1h par défaut)
- ✅ **Validation côté serveur** : Le backend valide les extensions et tailles
- ✅ **Contrôle d'accès** : Le backend gère qui peut uploader quoi

### Limitations de sécurité
- ⚠️ **Validation limitée** : Impossible de valider le contenu réel côté serveur
- ⚠️ **Size limits** : Pas de limite de taille côté Azure (à gérer côté front)

### Recommandations
1. **Valider côté front** : Taille, type, dimensions avant upload
2. **Rate limiting** : Limiter les demandes d'URLs d'upload
3. **Monitoring** : Logger tous les uploads pour détection d'abus
4. **Clean-up** : Script de nettoyage pour supprimer les uploads orphelins

## 📊 Monitoring

Le service inclut des logs détaillés :
- Génération d'URLs d'upload
- Vérifications d'upload
- Suppressions d'images
- Erreurs et échecs

Consultez les logs avec :
```bash
tail -f var/log/dev.log | grep AzureBlobImageService
```
