const sparePartsBrands = {
    // Repuestos para Tractores - Filtros
    tractor_filtro_aire: ['Mann+Hummel', 'Fleetguard', 'Donaldson', 'Baldwin', 'WIX', 'FRAM', 'Mahle', 'Luber-finer', 'Parker Filtration', 'K&N'],
    tractor_filtro_aceite: ['Mann+Hummel', 'Fleetguard', 'WIX', 'FRAM', 'Mahle', 'Baldwin', 'Donaldson', 'Luber-finer', 'Bosch', 'ACDelco'],
    tractor_filtro_combustible: ['Fleetguard', 'Mann+Hummel', 'Donaldson', 'Baldwin', 'Bosch', 'Delphi', 'Parker', 'WIX', 'FRAM', 'Mahle'],
    tractor_filtro_hidraulico: ['Hydac', 'Parker', 'Donaldson', 'Mann+Hummel', 'Fleetguard', 'Baldwin', 'Bosch Rexroth', 'MP Filtri', 'Mahle', 'Pall'],

    // Repuestos para Tractores - Correas
    tractor_correa_transmision: ['Gates', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Optibelt', 'Bando', 'SKF', 'Jason', 'PIX'],
    tractor_correa_alternador: ['Gates', 'Dayco', 'Continental', 'Bosch', 'SKF', 'Mitsuboshi', 'Goodyear', 'Bando', 'Optibelt', 'PIX'],

    // Repuestos para Tractores - Sistema Eléctrico
    tractor_bateria: ['Bosch', 'Varta', 'Exide', 'Optima', 'ACDelco', 'Interstate', 'Deka', 'Yuasa', 'Banner', 'Odyssey'],
    tractor_alternador: ['Bosch', 'Denso', 'Delco Remy', 'Prestolite', 'Valeo', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'Sawafuji'],
    tractor_motor_arranque: ['Bosch', 'Denso', 'Delco Remy', 'Valeo', 'Prestolite', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'WAI'],
    tractor_fusible: ['Littelfuse', 'Bussmann', 'Cooper Bussmann', 'Ferraz Shawmut', 'Schneider Electric', 'ABB', 'Siemens', 'Phoenix Contact', 'TE Connectivity', 'Mersen'],

    // Repuestos para Tractores - Neumáticos y Llantas
    tractor_neumatico: ['Firestone', 'Michelin', 'Goodyear', 'BKT', 'Alliance', 'Continental', 'Trelleborg', 'Titan', 'Mitas', 'Bridgestone'],
    tractor_llanta: ['Titan', 'GKN Wheels', 'Accuride', 'Maxion Wheels', 'Pronar', 'Birrana', 'Stalker', 'Grasdorf', 'Starco', 'Wheel Works'],

    // Repuestos para Tractores - Motor
    tractor_piston: ['Mahle', 'Federal-Mogul', 'NPR', 'Nural', 'Sealed Power', 'DNJ Engine Components', 'IPD', 'United Engine & Machine', 'Wiseco', 'Ross Racing Pistons'],
    tractor_bujia: ['NGK', 'Bosch', 'Denso', 'Champion', 'ACDelco', 'Motorcraft', 'Autolite', 'BERU', 'Splitfire', 'E3'],
    tractor_inyector: ['Bosch', 'Delphi', 'Denso', 'Continental/VDO', 'Siemens/VDO', 'Stanadyne', 'Yanmar', 'Zexel', 'Lucas', 'Caterpillar'],
    tractor_radiador: ['Modine', 'Valeo', 'Denso', 'Nissens', 'Behr', 'CSF', 'TYC', 'APDI', 'Spectra Premium', 'Vista-Pro'],

    // Repuestos para Tractores - Sistema Hidráulico
    tractor_bomba_hidraulica: ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Casappa', 'Bondioli & Pavesi', 'Sauer-Danfoss', 'Commercial', 'Prince', 'Cross'],
    tractor_manguera_hidraulica: ['Parker', 'Gates', 'Eaton', 'Bridgestone', 'Continental', 'Manuli', 'Alfagomma', 'Semperit', 'Dunlop', 'Goodyear'],
    tractor_valvula_hidraulica: ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Sun Hydraulics', 'Hydac', 'Walvoil', 'Prince', 'Cross', 'Brand'],

    // Repuestos para Cosechadoras
    cosechadora_cuchilla_trigo: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Deutz-Fahr', 'Laverda', 'Gleaner', 'Fendt'],
    cosechadora_cuchilla_maiz: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Capello', 'Olimac', 'Geringhoff', 'Drago', 'MacDon', 'Fantini'],
    cosechadora_cuchilla_arroz: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Kubota', 'Yanmar', 'ISEKI', 'Mitsubishi'],
    cosechadora_tamiz: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
    cosechadora_zaranda: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
    cosechadora_correa: ['Gates', 'Optibelt', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Bando', 'SKF', 'Jason', 'PIX'],
    cosechadora_cadena: ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
    cosechadora_rotor: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
    cosechadora_sacudidor: ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],

    // Repuestos para Equipos de Siembra
    siembra_disco: ['John Deere', 'Precision Planting', 'Kinze', 'Great Plains', 'Case IH', 'Monosem', 'Amazone', 'Lemken', 'Väderstad', 'Semeato'],
    siembra_cuchilla: ['Bellota Agrisolutions', 'Ingersoll', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Horsch', 'Amazone', 'Kuhn', 'Lemken'],
    siembra_dosificador: ['Precision Planting', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Horsch', 'Kuhn', 'MaterMacc'],
    siembra_tubo: ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Lemken', 'Horsch', 'Kuhn', 'Semeato'],
    siembra_boquilla: ['TeeJet', 'Hypro', 'Arag', 'Lechler', 'Albuz', 'Hardi', 'John Deere', 'Case IH', 'Amazone', 'Kuhn'],
    siembra_rodamiento: ['SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA', 'Koyo', 'NACHI', 'FYH', 'NKE'],
    siembra_eje: ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA'],

    // Repuestos para Equipos de Riego
    riego_aspersor: ['Nelson', 'Rain Bird', 'Hunter', 'Senninger', 'Komet', 'Netafim', 'Valley', 'Lindsay', 'Reinke', 'T-L'],
    riego_boquilla: ['Nelson', 'TeeJet', 'Senninger', 'Rain Bird', 'Hunter', 'Komet', 'Netafim', 'Hypro', 'Arag', 'Lechler'],
    riego_manguera: ['Netafim', 'John Deere Water', 'Rain Bird', 'Hunter', 'Toro', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Plastro'],
    riego_tuberia: ['Charlotte Pipe', 'JM Eagle', 'Netafim', 'Rain Bird', 'Hunter', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Georg Fischer'],
    riego_bomba: ['Grundfos', 'Xylem', 'KSB', 'Wilo', 'Franklin Electric', 'Berkeley', 'Cornell', 'Pentair', 'Goulds', 'Caprari'],
    riego_filtro: ['Amiad', 'Netafim', 'Arkal', 'Rain Bird', 'Hunter', 'Azud', 'STF', 'Hydro-Flow', 'Spin Klin', 'Sand Master'],

    // Repuestos para Equipos de Forraje
    forraje_cuchilla: ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
    forraje_martillo: ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Maschio', 'Pöttinger', 'Vogel & Noot', 'Seppi'],
    forraje_barra: ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
    forraje_pua_segadora: ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
    forraje_pua_empacadora: ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Massey Ferguson', 'Deutz-Fahr', 'Welger', 'Vicon'],
    forraje_cadena: ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
    forraje_engranaje: ['Martin Sprocket', 'Boston Gear', 'QTC Metric Gears', 'Browning', 'Hub City', 'Rexnord', 'Dodge', 'Baldor', 'SEW-Eurodrive', 'Nord']
};