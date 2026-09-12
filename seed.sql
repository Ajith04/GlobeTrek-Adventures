USE globetrek_adventures;
INSERT INTO users(full_name, email, phone, password_hash, role, status)
VALUES (
        'GlobeTrek Administrator',
        'admin@globetrek.lk',
        '0770000000',
        '$2y$12$Rdh91vY.oE3mwnqj5f8Wquknx81ZGUDAqsmLRBQ0EbQdNTf4nx7WC',
        'admin',
        'active'
    ),
    (
        'GlobeTrek Staff',
        'staff@globetrek.lk',
        '0770000001',
        '$2y$12$D7qmvMDYjX5pMglmhbZOKegPbDKc38eOqeeR/UbOBoUlwNdGphKti',
        'staff',
        'active'
    ) ON DUPLICATE KEY
UPDATE full_name =
VALUES(full_name),
    password_hash =
VALUES(password_hash),
    role =
VALUES(role),
    status = 'active';
INSERT INTO tour_packages(
        title,
        destination,
        duration_days,
        price,
        image_url,
        short_description,
        description,
        activities,
        inclusions,
        exclusions,
        status,
        created_by
    )
SELECT p.title,
    p.destination,
    p.duration_days,
    p.price,
    p.image_url,
    p.short_description,
    p.description,
    p.activities,
    p.inclusions,
    p.exclusions,
    'active',
    (
        SELECT id
        FROM users
        WHERE role = 'admin'
        ORDER BY id
        LIMIT 1
    )
FROM (
        SELECT 'Cultural Triangle Escape' title,
            'Sigiriya & Dambulla' destination,
            4 duration_days,
            89500 price,
            'https://images.unsplash.com/photo-1588598198321-9735fd52455b?auto=format&fit=crop&w=1400&q=80' image_url,
            'Ancient cities, cave temples and unforgettable rock-fortress views.' short_description,
            'Explore Sri Lanka’s cultural heart through Sigiriya, Dambulla and nearby heritage sites.' description,
            'Sigiriya rock fortress,Dambulla cave temple,Village tour,Sunset viewpoint' activities,
            'Accommodation,Breakfast,Private transfers,Guided sightseeing' inclusions,
            'Flights,Lunch and dinner,Personal expenses' exclusions
        UNION ALL
        SELECT 'Hill Country Adventure',
            'Kandy & Ella',
            5,
            112000,
            'assets/images/hill-country-adventure.jpg',
            'Tea country, scenic rail journeys and cool mountain air.',
            'Travel from Kandy to Ella through rolling tea estates, waterfalls and spectacular highland scenery.',
            'Temple of the Tooth,Scenic train ride,Tea factory,Nine Arch Bridge',
            'Accommodation,Breakfast,Train tickets,Private transfers',
            'Flights,Optional activities,Personal expenses'
        UNION ALL
        SELECT 'Southern Coast Retreat',
            'Galle & Mirissa',
            4,
            98000,
            'assets/images/southern-coast-retreat.png',
            'Fort history, golden beaches and relaxed southern-coast evenings.',
            'Enjoy the character of Galle Fort and unwind beside the beaches and bays of Mirissa.',
            'Galle Fort,Beach time,Sunset cruise,Local cuisine',
            'Accommodation,Breakfast,Private transfers,Selected activities',
            'Flights,Personal expenses,Travel insurance'
        UNION ALL
        SELECT 'Negombo Weekend',
            'Negombo',
            2,
            42000,
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?auto=format&fit=crop&w=1400&q=80',
            'A quick coastal break close to the airport.',
            'Discover Negombo’s lagoon, beach, fishing heritage and lively local food scene.',
            'Lagoon cruise,Fish market,Beach time,Local cuisine',
            'Accommodation,Breakfast,Airport transfer,Lagoon experience',
            'Flights,Personal expenses'
        UNION ALL
        SELECT 'Yala Wildlife Safari',
            'Yala',
            3,
            87500,
            'https://images.unsplash.com/photo-1535338454770-8be927b5a00b?auto=format&fit=crop&w=1400&q=80',
            'Jeep safaris and wild landscapes in leopard country.',
            'Venture into Yala National Park with experienced naturalists in search of elephants, leopards and birdlife.',
            'Morning safari,Evening safari,Birdwatching,Nature photography',
            'Accommodation,Breakfast,Safari jeep,Park entry',
            'Flights,Personal expenses,Optional meals'
        UNION ALL
        SELECT 'East Coast Beach Escape',
            'Trincomalee',
            4,
            96000,
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80',
            'Clear water, quiet beaches and easy tropical days.',
            'Relax on Sri Lanka’s east coast with time for swimming, snorkelling and Trincomalee highlights.',
            'Snorkelling,Beach time,Koneswaram Temple,Harbour viewpoint',
            'Accommodation,Breakfast,Private transfers,Snorkelling session',
            'Flights,Personal expenses,Travel insurance'
        UNION ALL
        SELECT 'Knuckles Mountain Trek',
            'Knuckles Range',
            4,
            105000,
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=80',
            'Guided trails through misty peaks and remote villages.',
            'Experience the biodiversity and dramatic scenery of the Knuckles Conservation Forest on guided hikes.',
            'Guided trekking,Waterfalls,Village walk,Birdwatching',
            'Accommodation,Meals during trek,Trek guide,Transfers',
            'Flights,Specialist equipment,Personal expenses'
        UNION ALL
        SELECT 'Anuradhapura Heritage Journey',
            'Anuradhapura',
            3,
            72000,
            'https://images.unsplash.com/photo-1562698013-ac13558052cd?auto=format&fit=crop&w=1400&q=80',
            'Sacred monuments and stories from an ancient capital.',
            'Walk and cycle among stupas, monasteries and reservoirs in one of Sri Lanka’s greatest historic cities.',
            'Sacred city tour,Cycling,Mihintale,Local lunch',
            'Accommodation,Breakfast,Guide,Private transfers',
            'Flights,Personal expenses,Optional activities'
        UNION ALL
        SELECT 'Arugam Bay Surf Break',
            'Arugam Bay',
            5,
            108000,
            'https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=1400&q=80',
            'Surf sessions, beach culture and laid-back east-coast living.',
            'Build confidence on the waves and enjoy the relaxed rhythm of Sri Lanka’s best-known surf town.',
            'Surf lessons,Beach time,Lagoon safari,Sunset viewpoint',
            'Accommodation,Breakfast,Three surf lessons,Board rental',
            'Flights,Personal expenses,Additional lessons'
        UNION ALL
        SELECT 'Udawalawe Elephant Trail',
            'Udawalawe',
            3,
            82000,
            'https://images.unsplash.com/photo-1557050543-4d5f4e07ef46?auto=format&fit=crop&w=1400&q=80',
            'Close encounters with elephants and open grassland scenery.',
            'Explore Udawalawe National Park and learn about elephant conservation in the surrounding region.',
            'Jeep safari,Elephant Transit Home,Birdwatching,Nature walk',
            'Accommodation,Breakfast,Safari jeep,Park entry',
            'Flights,Personal expenses,Optional meals'
        UNION ALL
        SELECT 'Jaffna Cultural Discovery',
            'Jaffna',
            4,
            94000,
            'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&w=1400&q=80',
            'Northern flavours, island temples and distinctive local heritage.',
            'Discover Jaffna’s forts, markets, cuisine and nearby islands on a thoughtfully paced northern journey.',
            'Jaffna Fort,Nallur Temple,Island excursion,Food tasting',
            'Accommodation,Breakfast,Private transfers,Local guide',
            'Flights,Personal expenses,Optional meals'
        UNION ALL
        SELECT 'Sri Lanka Grand Explorer',
            'Islandwide',
            10,
            265000,
            'https://images.unsplash.com/photo-1566296314736-6eaac1ca0cb9?auto=format&fit=crop&w=1400&q=80',
            'A complete journey through culture, hills, wildlife and coast.',
            'See Sri Lanka’s essential highlights on a balanced route from ancient cities to tea country and the southern coast.',
            'Sigiriya,Kandy,Ella,Yala,Galle,Beach time',
            'Accommodation,Breakfast,Private vehicle,Guided highlights',
            'Flights,Lunch and dinner,Travel insurance,Personal expenses'
    ) AS p
WHERE NOT EXISTS (
        SELECT 1
        FROM tour_packages existing
        WHERE existing.title = p.title
    );
INSERT INTO accommodations(name, location, price_per_night, description, status)
SELECT 'Jetwing Blue',
    'Negombo',
    25000,
    'Beachfront hotel partner.',
    'active'
WHERE NOT EXISTS(
        SELECT 1
        FROM accommodations
    );
INSERT INTO transportation_services(
        provider_name,
        service_type,
        price,
        description,
        status
    )
SELECT 'GlobeTrek Transfers',
    'Private airport transfer',
    8500,
    'Private car with airport pickup.',
    'active'
WHERE NOT EXISTS(
        SELECT 1
        FROM transportation_services
    );