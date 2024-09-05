# Usa l'immagine di Node.js come base
FROM node:latest

# Imposta la directory di lavoro all'interno del container
WORKDIR /app

# Copia il package.json e il package-lock.json nella directory di lavoro
COPY package*.json ./

# Installa le dipendenze
RUN npm install

# Copia il resto del codice sorgente nella directory di lavoro
COPY . .

# Esponi la porta 3000 per il server Next.js
EXPOSE 3000

# Avvia l'applicazione
CMD ["npm", "run", "dev"]

