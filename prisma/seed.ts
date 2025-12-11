
import { PrismaClient } from '@prisma/client';
import { INITIAL_USERS, INITIAL_ELECTRICITY, INITIAL_WATER, INITIAL_WASTE, INITIAL_UNITS, INITIAL_GOAL, INITIAL_WATER_GOAL, INITIAL_WASTE_GOAL } from '../constants';

const prisma = new PrismaClient();

async function main() {
    console.log('Start seeding ...');

    // Seed Settings
    // Check if settings exist, if not create
    const existingSettings = await prisma.systemSettings.findFirst();
    if (!existingSettings) {
        await prisma.systemSettings.create({
            data: {
                units: JSON.stringify(INITIAL_UNITS),
                electricityGoal: INITIAL_GOAL,
                waterGoal: INITIAL_WATER_GOAL,
                wasteGoal: INITIAL_WASTE_GOAL,
            },
        });
        console.log('Created System Settings');
    }

    // Seed Users
    for (const user of INITIAL_USERS) {
        const upsertUser = await prisma.user.upsert({
            where: { email: user.email },
            update: {},
            create: {
                id: user.id,
                name: user.name,
                email: user.email,
                password: user.password!, // Note: In production hash this!
                role: user.role,
                allowedUnits: JSON.stringify(user.allowedUnits || []),
            },
        });
        console.log(`Upserted user: ${upsertUser.email}`);
    }

    // Seed Electricity
    // Basic check to avoid duplicates on multiple runs (optional logic, trusting upsert or deleteMany if needed)
    // For simplicity, we won't delete, just create if empty.
    const elecCount = await prisma.electricityRecord.count();
    if (elecCount === 0) {
        for (const record of INITIAL_ELECTRICITY) {
            await prisma.electricityRecord.create({
                data: {
                    id: record.id,
                    date: record.date,
                    unit: record.unit,
                    cpflKwh: record.cpflKwh,
                    cpflCost: record.cpflCost,
                    floraKwh: record.floraKwh,
                    floraCost: record.floraCost,
                    floraSavings: record.floraSavings
                }
            })
        }
        console.log(`Created ${INITIAL_ELECTRICITY.length} electricity records`);
    }

    // Seed Water
    const waterCount = await prisma.waterRecord.count();
    if (waterCount === 0) {
        for (const record of INITIAL_WATER) {
            await prisma.waterRecord.create({
                data: {
                    id: record.id,
                    date: record.date,
                    unit: record.unit,
                    volume: record.volume,
                    cost: record.cost
                }
            })
        }
        console.log(`Created ${INITIAL_WATER.length} water records`);
    }

    // Seed Waste
    const wasteCount = await prisma.wasteRecord.count();
    if (wasteCount === 0) {
        for (const record of INITIAL_WASTE) {
            await prisma.wasteRecord.create({
                data: {
                    id: record.id,
                    date: record.date,
                    type: record.type,
                    category: record.category,
                    weight: record.weight,
                    financial: record.financial
                }
            })
        }
        console.log(`Created ${INITIAL_WASTE.length} waste records`);
    }

    console.log('Seeding finished.');
}

main()
    .then(async () => {
        await prisma.$disconnect();
    })
    .catch(async (e) => {
        console.error(e);
        await prisma.$disconnect();
        process.exit(1);
    });
