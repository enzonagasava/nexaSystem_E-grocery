<template>
    <div class="flex w-full justify-center bg-[#c9a875] px-10 py-12">
        <div class="w-full max-w-7xl">
            <h2 class="mb-12 text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">Como Comprar</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- Card 1 -->
                <div class="flex flex-col items-center justify-between rounded-2xl bg-white p-8 shadow-lg">
                    <div class="flex-1 text-center md:text-left">
                        <span class="mb-2 block text-lg font-semibold text-gray-900">Escolha a sua mercadoria.</span>
                        <p class="leading-relaxed text-gray-700">
                            Você pode escolher entre as diversas variedade que temos.<br />
                            Hoje trabalhamos com Shitake, Shimeji, Champignon e etc.
                        </p>
                    </div>
                    <img src="/images/como-comprar-1.png" alt="Cogumelos" class="mt-6 h-28 w-36 object-contain md:mt-0 md:ml-8" loading="lazy" />
                </div>

                <!-- Card 2 -->
                <div class="flex flex-col items-center justify-between rounded-2xl bg-white p-8 shadow-lg">
                    <img
                        src="/images/como-comprar-2.png"
                        alt="Pagamento"
                        class="order-2 mb-6 h-28 w-36 object-contain md:order-1 md:mr-8 md:mb-0"
                        loading="lazy"
                    />
                    <div class="order-1 flex-1 text-center md:text-right">
                        <span class="mb-2 block text-lg font-semibold text-gray-900">Faça o pedido</span>
                        <p class="leading-relaxed text-gray-700">
                            Faça o pedido pelo site ou whatsapp. Formas de pagamento, pix, cartão e dinheiro (na hora da entrega do produto.)
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="flex flex-col items-center justify-between rounded-2xl bg-white p-8 shadow-lg">
                    <div class="flex-1 text-center md:text-left">
                        <span class="mb-2 block text-lg font-semibold text-gray-900">Defina o ponto de entrega.</span>
                        <p class="leading-relaxed text-gray-700">
                            Pelo mapa de entrega, verifique o dia e regiões que temos disponíveis para a entrega.
                        </p>
                    </div>
                    <img src="/images/como-comprar-3.png" alt="Mapa" class="mt-6 h-28 w-36 object-contain md:mt-0 md:ml-8" loading="lazy" />
                </div>

                <!-- Card 4 -->
                <div class="flex flex-col items-center justify-between rounded-2xl bg-white p-8 shadow-lg">
                    <img
                        src="/images/como-comprar-4.png"
                        alt="Entrega"
                        class="order-2 mb-6 h-28 w-36 object-contain md:order-1 md:mr-8 md:mb-0"
                        loading="lazy"
                    />
                    <div class="order-1 flex-1 text-center md:text-right">
                        <span class="block text-lg font-semibold text-gray-900"> Pronto, seu produto chegará em fresco em sua casa. </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts"></script>

<style scoped></style>
