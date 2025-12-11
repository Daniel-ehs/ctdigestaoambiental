
const express = require('express');
const cors = require('cors');
const path = require('path');
const { PrismaClient } = require('@prisma/client');

const app = express();
const prisma = new PrismaClient();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

// API Routes

// --- Users ---
app.post('/api/login', async (req, res) => {
    const { email, password } = req.body;
    try {
        const user = await prisma.user.findUnique({ where: { email } });
        if (user && user.password === password) { // Note: Validate hash in prod
            // Remove password from response
            const { password, ...userWithoutPass } = user;
            res.json(userWithoutPass);
        } else {
            res.status(401).json({ error: 'Invalid credentials' });
        }
    } catch (error) {
        res.status(500).json({ error: 'Login failed' });
    }
});

app.get('/api/users', async (req, res) => {
    const users = await prisma.user.findMany();
    const sanitized = users.map(u => {
        const { password, ...rest } = u;
        return {
            ...rest,
            allowedUnits: u.allowedUnits ? JSON.parse(u.allowedUnits) : []
        };
    });
    res.json(sanitized);
});

app.post('/api/users', async (req, res) => {
    try {
        const userData = { ...req.body };
        if (userData.allowedUnits) {
            userData.allowedUnits = JSON.stringify(userData.allowedUnits);
        }
        const user = await prisma.user.create({ data: userData });
        // Return with parsed array
        const { password, ...rest } = user;
        res.json({
            ...rest,
            allowedUnits: user.allowedUnits ? JSON.parse(user.allowedUnits) : []
        });
    } catch (e) {
        console.error(e);
        res.status(500).json({ error: 'Failed to create user' });
    }
});

app.put('/api/users/:id', async (req, res) => {
    try {
        const userData = { ...req.body };
        if (userData.allowedUnits) {
            userData.allowedUnits = JSON.stringify(userData.allowedUnits);
        }
        const user = await prisma.user.update({
            where: { id: req.params.id },
            data: userData
        });
        const { password, ...rest } = user;
        res.json({
            ...rest,
            allowedUnits: user.allowedUnits ? JSON.parse(user.allowedUnits) : []
        });
    } catch (e) {
        console.error(e);
        res.status(500).json({ error: 'Failed to update user' });
    }
});

app.delete('/api/users/:id', async (req, res) => {
    try {
        await prisma.user.delete({ where: { id: req.params.id } });
        res.json({ success: true });
    } catch (e) {
        res.status(500).json({ error: 'Failed to delete user' });
    }
});

// --- Settings ---
app.get('/api/settings', async (req, res) => {
    const settings = await prisma.systemSettings.findFirst();
    if (settings) {
        res.json({
            ...settings,
            units: settings.units ? JSON.parse(settings.units) : []
        });
    } else {
        res.json({ units: [], electricityGoal: 0, waterGoal: 0, wasteGoal: 0 });
    }
});

app.post('/api/settings', async (req, res) => {
    const settingsData = { ...req.body };
    if (settingsData.units) {
        settingsData.units = JSON.stringify(settingsData.units);
    }

    const first = await prisma.systemSettings.findFirst();
    if (first) {
        const updated = await prisma.systemSettings.update({
            where: { id: first.id },
            data: settingsData
        });
        res.json({
            ...updated,
            units: updated.units ? JSON.parse(updated.units) : []
        });
    } else {
        const created = await prisma.systemSettings.create({ data: settingsData });
        res.json({
            ...created,
            units: created.units ? JSON.parse(created.units) : []
        });
    }
});

// --- Electricity ---
app.get('/api/electricity', async (req, res) => {
    const data = await prisma.electricityRecord.findMany();
    res.json(data);
});

app.post('/api/electricity', async (req, res) => {
    try {
        const data = await prisma.electricityRecord.create({ data: req.body });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.put('/api/electricity/:id', async (req, res) => {
    try {
        const data = await prisma.electricityRecord.update({
            where: { id: req.params.id },
            data: req.body
        });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.delete('/api/electricity/:id', async (req, res) => {
    try {
        await prisma.electricityRecord.delete({ where: { id: req.params.id } });
        res.json({ success: true });
    } catch (e) { res.status(500).json({ error: e }); }
});

// --- Water ---
app.get('/api/water', async (req, res) => {
    const data = await prisma.waterRecord.findMany();
    res.json(data);
});

app.post('/api/water', async (req, res) => {
    try {
        const data = await prisma.waterRecord.create({ data: req.body });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.put('/api/water/:id', async (req, res) => {
    try {
        const data = await prisma.waterRecord.update({
            where: { id: req.params.id },
            data: req.body
        });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.delete('/api/water/:id', async (req, res) => {
    try {
        await prisma.waterRecord.delete({ where: { id: req.params.id } });
        res.json({ success: true });
    } catch (e) { res.status(500).json({ error: e }); }
});

// --- Waste ---
app.get('/api/waste', async (req, res) => {
    const data = await prisma.wasteRecord.findMany();
    res.json(data);
});

app.post('/api/waste', async (req, res) => {
    try {
        const data = await prisma.wasteRecord.create({ data: req.body });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.put('/api/waste/:id', async (req, res) => {
    try {
        const data = await prisma.wasteRecord.update({
            where: { id: req.params.id },
            data: req.body
        });
        res.json(data);
    } catch (e) { res.status(500).json({ error: e }); }
});

app.delete('/api/waste/:id', async (req, res) => {
    try {
        await prisma.wasteRecord.delete({ where: { id: req.params.id } });
        res.json({ success: true });
    } catch (e) { res.status(500).json({ error: e }); }
});

// Serve Static Files (Production)
if (process.env.NODE_ENV === 'production') {
    app.use(express.static(path.join(__dirname, 'dist')));

    app.get('*', (req, res) => {
        res.sendFile(path.join(__dirname, 'dist', 'index.html'));
    });
}

const server = app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
